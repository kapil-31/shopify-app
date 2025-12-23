<?php
// app/Http/Middleware/ShopifyAuth.php
namespace App\Http\Middleware;

use App\Http\Controllers\Webhooks\ProductWebhookController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ShopifyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('id_token');
        $registerWebhook = false;
        if ($token) {
            $payload = JWT::decode($token, new Key(config('services.shopify.secret'), 'HS256'));
            if ($payload->aud == config('services.shopify.key')) {
                $shop = str_replace(['https://', '/admin'], '', $payload->dest ?? $payload->iss);

                $exchange = $this->accessTokenExchange($shop, $token);
                if ($exchange->successful()) {
                    $accessToken = $exchange->json('access_token');

                    //  injecting webhook here register here webhook->delete reinstall app comes here
                    if (!Shop::where('shop', $shop)->exists()) {
                        $registerWebhook = true;
                    }
                    Shop::updateOrCreate(['shop' => $shop], ['access_token' => $accessToken]);
                    if ($registerWebhook) {
                        app(ProductWebhookController::class)->registerWebhooks($shop);
                    }
                } else {
                    return redirect("/auth?shop={$shop}");
                }
                $request->merge(['shopify_access_token' => $accessToken]);

                return $next($request);
            }
        }
        abort('Unauthorized request');
    }

    public function accessTokenExchange($shop, $token)
    {

        return Http::asForm()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('services.shopify.key'),
            'client_secret' => config('services.shopify.secret'),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
            'subject_token' => $token,
            'subject_token_type' => 'urn:ietf:params:oauth:token-type:id_token',
            'requested_token_type' => 'urn:shopify:params:oauth:token-type:offline-access-token',
        ]);
    }
}
