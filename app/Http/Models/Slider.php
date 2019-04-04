<?php

namespace App\Api\V1\Models;

class Slider extends ApiModel
{

    protected $table = 'sliders';

    protected $fillable = [
        'label',
        'status'
    ];

    public function slides()
    {
        return $this->hasMany($this->ns.'\Slide');
    }

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/sliders/' .$this->id;
    }

}