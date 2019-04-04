<?php

namespace App\Api\V1\Models;

class CustomerAddress extends ApiModel
{
    protected $table = 'customer_address';

    protected $primaryKey = 'id';

    protected $fillable = [
        'label',
        'customer_id',
        'status',
        'country',
        'postal_code',
        'state',
        'line1',
        'line2',
        'lat',
        'long',
        'telephone',
        'instructions',
        'photo_uri',
        'city',
        'delivery_area_id'
    ];

    public function customer()
    {
        return $this->belongsTo($this->ns.'\Customer');
    }

    public function deliveryArea()
    {
        return $this->belongsTo($this->ns.'\DeliveryArea','delivery_area_id');
    }

    public function getUriAttribute()
    {
        return $this->customer->uri . '/addresses/' . $this->id;
    }

    /**
     * added a scope for enabled address only
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled',1);
    }
}