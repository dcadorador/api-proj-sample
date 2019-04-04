<?php

namespace App\Api\V1\Models;

use Carbon\Carbon;

class DeliveryArea extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    protected $table = 'location_delivery_areas';

    protected $fillable = [
        'location_id',
        'label',
        'code',
        'coordinates'
    ];

    public function location()
    {
        return $this->belongsTo($this->ns.'\Location');
    }

    public function getUriAttribute()
    {
        return $this->location->uri . '/delivery-areas/' . $this->id;
    }
}