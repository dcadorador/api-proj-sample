<?php

namespace App\Api\V1\Models;


class Application extends ApiModel
{
    protected $table = 'applications';

    protected $fillable = [
        'label',
        'concept_id',
        'google_arn',
        'apple_arn',
        'web_arn',
        'broadcast_topic_arn'
    ];

    public function getUriAttribute()
    {
        return $this->uri . '/applications/' . $this->id;
    }

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function customerDevices()
    {
        return $this->hasMany($this->ns.'\CustomerDevice');
    }
}