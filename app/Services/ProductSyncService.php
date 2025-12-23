<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
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


     public function sync(string $shopDomain): int
    {
        $token = Shop::where('shop', $shopDomain)->value('access_token');

         if (!$token) {
            throw new \Exception('Shop not installed');
        }


        $query = <<<GQL
        query getProducts(\$first: Int!, \$after: String) {
          products(first: \$first, after: \$after) {
            edges {
              cursor
              node {
                id
                title
                status
              }
            }
            pageInfo {
              hasNextPage
            }
          }
        }
        GQL;

        $after = null;
        $count = 0;

        do {
            $response = $this->graphql->query(
                $shopDomain,
                $token,
                $query,
                ['first' => 50, 'after' => $after]
            );


            $products = $response['data']['products']['edges'];
            $pageInfo = $response['data']['products']['pageInfo'];

            dd($products);

            foreach ($products as $edge) {
                $node = $edge['node'];

                Product::updateOrCreate(
                    ['shopify_product_id' => $this->extractId($node['id'])],
                    [
                        'shop' => $shopDomain,
                        'title' => $node['title'],
                        'status' => $node['status'],
                        'last_sync'=> now(),
                    ]
                );

                $count++;
                $after = $edge['cursor'];
            }

        } while ($pageInfo['hasNextPage'] ?? false);

        return $count;
    }

     private function extractId(string $gid): int
    {
       
        return (int) basename($gid);
    }
}
