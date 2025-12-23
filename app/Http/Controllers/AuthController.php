<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function __construct(private WebhookService $webhookService)
    {
    
    }
    public function redirectToShopify(Request $request)
    {
        $shop = $request->query('shop');


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
        Auth::login($shop);

        $this->webhookService->registerWebhooks($shop->shop);

        return redirect()->route('home');
    }
}
