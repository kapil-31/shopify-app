<?php
namespace App\Http\Controllers;

use App\Models\Collection;

class CollectionController extends Controller
{
     public function collections()
    {
        $shopId = auth()->user()->id;

        $collections = Collection::where('shop_id', $shopId)
            ->select('id', 'title')
            ->withCount('products') 
            ->orderBy('title')
            ->get();

        return response()->json($collections);
    }
}
