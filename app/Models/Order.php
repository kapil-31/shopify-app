<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
     protected $fillable = [
        'shop',
        'shopify_order_id',
        'name',
        'financial_status',
        'fulfillment_status',
        'total_price',
    ];
}
