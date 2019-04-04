<?php

namespace App\Api\V1\Models;

class Checkout extends ApiModel
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'checkout_id'
    ];


    public function order()
    {
        return $this->belongsTo($this->ns.'\Order');
    }

    public function customer()
    {
        return $this->hasMany($this->ns.'\Customer');
    }
}