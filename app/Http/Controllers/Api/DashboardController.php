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
        return response()->json([
            'total_products' => Product::count(),
            'total_collections'=> Collection::count(),
            'last_sync' => SyncLogger::latest('last_sync')->value('last_sync')?->diffForHumans(),
        ]);
    }

   
}
