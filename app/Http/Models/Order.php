<?php

namespace App\Api\V1\Models;


class Order extends ApiModel
{

    protected $fillable = [
        'concept_id',
        'reference',
        'code',
        'promised_time',
        'source',
        'type',
        'subtotal',
        'customer_address_id',
        'customer_id',
        'device_id',
        'location_id',
        'customer_favorite',
        'total',
        'discount',
        'delivery_charge',
        'coupon_code',
        'payment_type',
        'vat_amount',
        'is_posted',
        'order_pos_response',
        'notes',
        'finished_at'
    ];

    public function customer()
    {
        return $this->belongsTo($this->ns.'\Customer');
    }

    public function location()
    {
        return $this->belongsTo($this->ns.'\Location');
    }

    public function customerAddress()
    {
        return $this->belongsTo($this->ns.'\CustomerAddress');
    }

    public function payments()
    {
        return $this->hasOne($this->ns.'\Payment');
    }

    public function statuses()
    {
        return $this->hasMany($this->ns.'\OrderOrderStatus')->orderBy('created_at', 'DESC');
    }

    public function currentStatus()
    {
        return $this->statuses()->take(1);
    }

    public function orderItems()
    {
        return $this->hasMany($this->ns.'\ItemOrder');
    }

    public function orderCheckouts()
    {
        return $this->hasMany($this->ns.'\Checkout');
    }

    public function orderRatings()
    {
        return $this->hasMany($this->ns.'\OrderRating', 'order_id');
    }

    public function employees()
    {
        return $this->belongsToMany($this->ns.'\Employee');
    }

    public function concept() 
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/orders/' . $this->id;
    }

    public function driver()
    {
        return $this->belongsToMany($this->ns.'\Employee')->wherePivot('function','driver')->orderBy('employee_order.created_at','DESC');
    }

}