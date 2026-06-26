<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $fillable = ['name', 'description', 'base_price', 'photo', 'is_active'];
}
