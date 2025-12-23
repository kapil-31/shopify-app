<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Webhooks\ProductWebhookController;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct() {}
    public function redirectToShopify(Request $request)
    {
        $shop = $request->query('shop');
        Log::info('redirectToShopify', ['data' => $shop]);


        abort_unless($shop, 400, 'Missing shop parameter');


        $query = http_build_query([
            'client_id' => config('services.shopify.key'),
            'scope' => config('services.shopify.scopes'),
            'redirect_uri' => url(config('services.shopify.redirect')),
            'state' => csrf_token(),
        ]);


        return redirect("https://{$shop}/admin/oauth/authorize?$query");
    }
    public function handleCallback(Request $request)
    {

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::post("https://{$request->shop}/admin/oauth/access_token", [
            'client_id' => config('services.shopify.key'),
            'client_secret' => config('services.shopify.secret'),
            'code' => $request->code,
        ]);

        $token = $response->json('access_token');

        $shop =  Shop::updateOrCreate(
            ['shop' => $request->shop],
            ['access_token' => $token]
        );
        // registeringwebhooks 
        app(ProductWebhookController::class)->registerWebhooks($shop->shop);


        Log::info('user_thirdd___user', ['shop' => $shop]);



        return redirect()->route('home');
    }
}
