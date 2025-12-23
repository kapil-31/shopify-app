<?php

namespace App\Http\Controllers;

use App\Services\ProductSyncService;
use Illuminate\Http\Request;

class ProductSyncController extends Controller
{
    public function __construct(
        private ProductSyncService $syncService
    ) {}

   
    public function sync(Request $request)
    {
       
        $shop = auth()->user()->shop;
        $count = $this->syncService->sync($shop);

        return response()->json([
            'message' => 'Products synced successfully',
            'count'   => $count,
        ]);
    }
}
