<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;

class VerifyShopifySessionToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Missing session token'], 401);
        }

        try {
            $payload = JWT::decode(
                $token,
                new Key(config('services.shopify.secret'), 'HS256')
            );

            if (!str_ends_with($payload->iss, '.myshopify.com/admin')) {
                return response()->json(['error' => 'Invalid issuer'], 401);
            }

            if ($payload->aud !== config('services.shopify.key')) {
                return response()->json(['error' => 'Invalid audience'], 401);
            }

            $shop = str_replace(['https://', '/admin'], '', $payload->dest ?? $payload->iss);

            $shopModel = \App\Models\Shop::where('shop', $shop)->first();
            if (!$shopModel) {
                return response()->json(['error' => 'Shop not found'], 401);
            }

            $request->merge([
                'current_shop' => $shop,
                'shop_model' => $shopModel,
            ]);

            auth()->login($shopModel);

            return $next($request);

        } catch (SignatureInvalidException $e) {
            return response()->json(['error' => 'Invalid signature'], 401);
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);
        } catch (BeforeValidException $e) {
            return response()->json(['error' => 'Token not yet valid'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}