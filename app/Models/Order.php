<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'customer_id',
        'service_id',
        'item_type_id',
        'courier_id',
        'pickup_courier_id',
        'delivery_courier_id',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'pickup_time',
        'notes',
        'service_price',
        'item_price',
        'shipping_cost',
        'tax',
        'total_price',
        'status',
        'payment_status',
        'stripe_session_id',
        'soap',
        'fragrance',
        'payment_method'
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function pickupCourier()
    {
        return $this->belongsTo(User::class, 'pickup_courier_id');
    }

    public function deliveryCourier()
    {
        return $this->belongsTo(User::class, 'delivery_courier_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at', 'asc');
    }

    public function photos()
    {
        return $this->hasMany(OrderPhoto::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class)->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
