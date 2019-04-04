<?php

namespace App\Api\V1\Models;

class Client extends ApiModel
{

    public function concepts()
    {
        return $this->hasMany($this->ns.'\Concept');
    }

    public function reseller() 
    {
        return $this->belongsTo($this->ns.'\Reseller');
    }

    public function getUriAttribute() {
        return $this->getBaseUri() . '/clients/' . $this->id;
    }

}