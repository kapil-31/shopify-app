<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLogger extends Model
{
    protected $fillable = [ 'last_sync','shop_id'];
    protected $casts = ['last_sync'=>'datetime'];
}
