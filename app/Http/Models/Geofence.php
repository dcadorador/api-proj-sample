<?php

namespace App\Api\V1\Models;

class Geofence extends ApiModel
{
    protected $table = 'geofences';

    protected $fillable = [
        'location_id',
        'inner',
        'outer'
    ];

    public function location()
    {
        return $this->belongsTo($this->ns.'\Location');
    }

    public function getUriAttribute()
    {
        return $this->location->uri .'/geofences';
    }

}