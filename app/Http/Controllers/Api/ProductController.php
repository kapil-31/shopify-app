<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepository $products
    ) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->products->paginate($request)
        );
    }
}