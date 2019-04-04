<?php

namespace App\Api\V1\Models;

class ExternalOrder extends ApiModel
{
    protected $table = 'external_orders';

    protected $fillable = [
        'concept_id',
        'location_id',
        'order_hid',
        'order_time',
        'promised_time',
        'total',
        'customer_hid',
        'customer',
        'customer_phone',
        'delivery_hid',
        'delivery_address',
        'delivery_address_longitude',
        'delivery_address_latitude',
        'payment_hid',
        'payment_amount',
        'payment_method',
        'payment_date'
    ];

    public function location()
    {
        return $this->belongsTo($this->ns.'\Location');
    }

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function statuses()
    {
        return $this->belongsToMany($this->ns.'\ExternalOrderOrderStatus','external_order_order_status','external_order_id','external_order_status_id')->orderBy('created_at', 'DESC');
    }

    public function currentStatus()
    {
        return $this->statuses()->take(1);
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/external-orders/' . $this->id;
    }
}