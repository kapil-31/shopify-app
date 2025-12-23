<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $shop = auth()->user(); // Shop model
        $token = $shop->access_token;

        // Smart collections
        $smart = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
        ])->get("https://{$shop->shop_domain}/admin/api/2024-01/smart_collections.json")
          ->json('smart_collections') ?? [];

        // Custom collections
        $custom = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
        ])->get("https://{$shop->shop_domain}/admin/api/2024-01/custom_collections.json")
          ->json('custom_collections') ?? [];

        return response()->json([
            'smart_collections' => $smart,
            'custom_collections' => $custom,
        ]);
    }
}
