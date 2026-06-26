<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['order_id', 'rating', 'rating_service', 'rating_courier', 'comment'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
