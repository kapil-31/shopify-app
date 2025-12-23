<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class DashboardController extends Controller
{
    public function summary()
    {
        return response()->json([
            'total_products' => Product::count(),
            'last_sync' => Product::latest('last_sync')->value('last_sync')?->diffForHumans(),
        ]);
    }
}
