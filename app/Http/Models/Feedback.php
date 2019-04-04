<?php

namespace App\Api\V1\Models;

class Feedback extends ApiModel
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'concept_id',
        'name',
        'email',
        'telephone',
        'subject',
        'body',
        'image_uri',
        'customer_id',
        'user_agent'
    ];

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/feedbacks/' . $this->id;
    }

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function customer()
    {
        return $this->belongsTo($this->ns.'\Customer');
    }

    public function ratings()
    {
        return $this->hasMany($this->ns.'\OrderRating');
    }
}