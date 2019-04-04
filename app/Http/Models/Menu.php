<?php

namespace App\Api\V1\Models;

class Menu extends ApiModel
{

    protected $fillable = [
        'code',
        'concept_id',
        'label',
        'key'
    ];

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function categories() 
    {
        return $this->hasMany($this->ns.'\Category');
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/menus/' .$this->id;
    }

}