<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Shop extends  Authenticatable
{
    protected $fillable = ['shop','access_token'];
    protected $primaryKey = 'shop';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $hidden = ['access_token'];
   
}
