<?php

namespace App\Api\V1\Models;


class CustomerDevice extends ApiModel
{
    protected $table = 'customer_devices';

    protected $fillable = [
        'customer_id',
        'application_id',
        'device_token',
        'device_id',
        'endpoint_arn',
        'topic_subscription_arn',
        'model'
    ];

    public function customer()
    {
        return $this->belongsTo($this->ns.'\Customer');
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/devices/' . $this->id;
    }
}
