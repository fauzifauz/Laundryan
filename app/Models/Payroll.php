<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'bonus',
        'potongan',
        'month',
        'year',
        'status',
        'stripe_transfer_id',
        'payment_method',
        'payment_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
