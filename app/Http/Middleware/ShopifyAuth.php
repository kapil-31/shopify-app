<?php
// app/Http/Middleware/ShopifyAuth.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use Illuminate\Support\Str;

class ShopifyAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        $shop = $request->query('shop');
        

        if (!$shop && $request->query('host')) {
            $decoded = base64_decode($request->query('host'));
            $shop = Str::beforeLast($decoded, '/admin');
        }
        if (!$shop) {
            return redirect('/auth');
        }

        $shopModel = Shop::where('shop', $shop)->first();

        if ($shopModel) {
            Auth::login($shopModel);
            return $next($request);
        }

        return redirect("/auth?shop={$shop}");
    }
}