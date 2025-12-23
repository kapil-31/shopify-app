<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;



Route::get('/auth', [AuthController::class, 'redirectToShopify']);
Route::get('/auth/callback', [AuthController::class, 'handleCallback']);

Route::get('/{any?}', function () {
    return view('app');
})
->where('any', '.*')
->middleware('shopify.auth')
->name('home');


