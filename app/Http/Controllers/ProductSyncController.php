<?php

namespace App\Http\Controllers;

use App\Services\ProductSyncService;
use Illuminate\Http\Request;

class ProductSyncController extends Controller
{
    public function __construct(
        private ProductSyncService $syncService
    ) {}

    // public function sync()
    // {
    //     $shop = 'assessment-app.myshopify.com';
    //     $token = Shop::where('shop', $shop)->value('access_token');
    //     if (!$token) {
    //         abort(403, 'Shop not installed');
    //     }
    //     $response = Http::withHeaders([
    //         'X-Shopify-Access-Token' => $token,
    //     ])->get("https://{$shop}/admin/api/2025-10/products.json");

    //     $products = $response->json('products');

    //     foreach ($products as $item) {
    //         Product::updateOrCreate(
    //             [
    //                 'shop' => $shop,
    //                 'shopify_product_id' => $item['id'],
    //             ],
    //             [
    //                 'title' => $item['title'],
    //                 'status' => $item['status'],
    //             ]
    //         );
    //     }

    //     return response()->json([
    //         'synced' => count($products),
    //     ]);
    // }

    public function sync(Request $request)
    {
       
        $shop = auth()->user()->shop;
        $count = $this->syncService->sync($shop);

        return response()->json([
            'message' => 'Products synced successfully',
            'count'   => $count,
        ]);
    }
}
