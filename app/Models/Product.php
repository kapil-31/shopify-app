<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'shop',
        'shopify_product_id',
        'title',
        'status',
        'last_sync'
    ];
    protected $casts = ['last_sync'=> 'datetime'];
}
