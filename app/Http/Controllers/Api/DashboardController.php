<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Models\SyncLogger;

class DashboardController extends Controller
{
    public function summary()
    {
        $shop_id = auth()->user()->id;
        return response()->json([
            'total_products' => Product::where('shop_id',$shop_id)->count(),
            'total_collections'=> Collection::where('shop_id',$shop_id)->count(),
            'last_sync' => SyncLogger::where('shop_id',$shop_id)->latest('last_sync')->value('last_sync')?->diffForHumans(),
        ]);
    }

   
}
