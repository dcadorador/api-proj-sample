<?php

namespace App\Api\V1\Models;

class Payment extends ApiModel
{

    protected $fillable = [
        'code',
        'order_id',
        'method',
        'amount',
        'cash_presented',
        'payment_reference_number',
        'tip',
        'status',
        'last_4_digits',
        'merchant_reference'
    ];

    public function order()
    {
        return $this->belongsTo($this->ns.'\Order');
    }

    public function getUriAttribute() 
    {
        return $this->order->uri . '/payments/' . $this->id;
    }
}