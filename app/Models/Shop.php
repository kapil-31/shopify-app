<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Shop extends  Authenticatable
{
    protected $fillable = ['shop','access_token'];
    protected $hidden = ['access_token'];
   
}
