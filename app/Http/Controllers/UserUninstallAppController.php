<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserUninstallAppController extends Controller
{
     public function handle(Request $request)
    {
        $this->verify($request);
        $data = $request->getContent();
        $payload = json_decode($data, true);
        $shopDomain = $payload['domain'];

        Log::info("User Uninstalled App",['data'=> $payload]);
        Shop::where('shop', $shopDomain)->delete();

        
    }
     private function verify(Request $request): void
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();

        $calculated = base64_encode(
            hash_hmac('sha256', $data, config('services.shopify.secret'), true)
        );

        if (!hash_equals($hmac, $calculated)) {
            abort(401);
        }
    }
}
