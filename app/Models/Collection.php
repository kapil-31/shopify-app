<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'shop_id',
        'shopify_collection_id',
        'title',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
           
        );
    }
}
