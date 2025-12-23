<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Product;
use App\Models\Shop;
use App\Models\SyncLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as FacadesLog;

class ProductSyncService
{
    public function __construct(
        private ShopifyGraphqlService $graphql
    ) {}

    public function handleWebhook(Request $request): void
    {
        $topic   = $request->header('X-Shopify-Topic');
        FacadesLog::info('Webhook topic', ['message' => $topic]);
        $payload = json_decode($request->getContent(), true);
        match ($topic) {
            'products/create' => $this->handleProductUpsert($payload),
            'products/update' => $this->handleProductUpsert($payload),
            'products/delete' => $this->handleProductDelete($payload),
            default => null,
        };
    }

    private function handleProductUpsert(array $payload): void
    {
        Product::updateOrCreate(
            ['shopify_product_id' => $payload['id']],
            [
                'title'  => $payload['title'],
                'status' => $payload['status'],
            ]
        );
    }

    private function handleProductDelete(array $payload): void
    {
        Product::where('shopify_product_id', $payload['id'])->delete();
    }


    //  public function sync(string $shopDomain): int
    // {
    //     $token = Shop::where('shop', $shopDomain)->value('access_token');

    //      if (!$token) {
    //         throw new \Exception('Shop not installed');
    //     }


    //     $query = <<<GQL
    //     query getProducts(\$first: Int!, \$after: String) {
    //       products(first: \$first, after: \$after) {
    //         edges {
    //           cursor
    //           node {
    //             id
    //             title
    //             status
    //           }
    //         }
    //         pageInfo {
    //           hasNextPage
    //         }
    //       }
    //     }
    //     GQL;

    //     $after = null;
    //     $count = 0;

    //     do {
    //         $response = $this->graphql->query(
    //             $shopDomain,
    //             $token,
    //             $query,
    //             ['first' => 50, 'after' => $after]
    //         );


    //         $products = $response['data']['products']['edges'];
    //         $pageInfo = $response['data']['products']['pageInfo'];

    //         dd($products);

    //         foreach ($products as $edge) {
    //             $node = $edge['node'];

    //             Product::updateOrCreate(
    //                 ['shopify_product_id' => $this->extractId($node['id'])],
    //                 [
    //                     'shop' => $shopDomain,
    //                     'title' => $node['title'],
    //                     'status' => $node['status'],
    //                     'last_sync'=> now(),
    //                 ]
    //             );

    //             $count++;
    //             $after = $edge['cursor'];
    //         }

    //     } while ($pageInfo['hasNextPage'] ?? false);

    //     return $count;
    // }

     private function extractId(string $gid): int
    {
       
        return (int) basename($gid);
    }


     public function sync() 
    { 
        // pending using queues and jobs
        $shop = auth()->user();


        $query = <<<'GRAPHQL'
query getProducts($first: Int!, $after: String) {
  products(first: $first, after: $after) {
    edges {
      cursor
      node {
        id
        title
        status
        collections(first: 10) {
          edges {
            node {
              id
              title
            }
          }
        }
      }
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
GRAPHQL;

        $cursor = null;

        do {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->access_token,
                'Content-Type' => 'application/json',
            ])->post(
                "https://{$shop->shop}/admin/api/2024-01/graphql.json",
                [
                    'query' => $query,
                    'variables' => [
                        'first' => 50,
                        'after' => $cursor,
                    ],
                ]
            )->json();


            $products = $response['data']['products']['edges'];
            $pageInfo = $response['data']['products']['pageInfo'];

            foreach ($products as $edge) {
                $node = $edge['node'];

                $productId = (int) basename($node['id']);

                $product = Product::updateOrCreate(
                    [
                        'shop_id' => $shop->id,
                        'shopify_product_id' => $productId,
                    ],
                    [
                        'title' => $node['title'],
                        'status' => $node['status'],
                    ]
                );

                foreach ($node['collections']['edges'] as $cEdge) {
                    $cNode = $cEdge['node'];
                    $collectionId = (int) basename($cNode['id']);

                    $collection = Collection::updateOrCreate(
                        [
                            'shop_id' => $shop->id,
                            'shopify_collection_id' => $collectionId,
                        ],
                        [
                            'title' => $cNode['title'],
                        ]
                    );

                    DB::table('collection_product')->updateOrInsert([
                        'collection_id' => $collection->id,
                        'product_id' => $product->id,
                    ]);
                }
            }

            $cursor = $pageInfo['hasNextPage']
                ? $pageInfo['endCursor']
                : null;
        } while ($cursor);

        SyncLogger::insert(['last_sync'=>now()]);

        return response()->json(['status' => 'synced']);
    }
}
