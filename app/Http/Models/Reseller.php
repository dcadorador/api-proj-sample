<?php

namespace App\Api\V1\Models;

class Reseller extends ApiModel
{

    protected $fillable = [
        'label',
        'host',
        'cname',
        'logo_uri',
        'theme'
    ];

    public function clients()
    {
        return $this->hasMany($this->ns.'\Client');
    }

}