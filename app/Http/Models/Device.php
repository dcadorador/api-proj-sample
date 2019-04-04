<?php

namespace App\Api\V1\Models;

class Device extends ApiModel
{

    protected $fillable = [
        'location_id',
        'label',
        'last_heartbeat_at',
        'tasks',
        //'mac_address',
        'device_uuid'
    ];

    public function location()
    {
        return $this->belongsTo($this->ns . '\Location');
    }

    public function user()
    {
    	return $this->morphOne($this->ns . '\ApiSubscriber', 'userable');
    }

    public function getMacAddressAttribute() 
    {
        return $this->user == null? '' : $this->user->username;
    }

    public function getUriAttribute()
    {
        return $this->location->uri . '/devices/'. $this->id;
    }

}