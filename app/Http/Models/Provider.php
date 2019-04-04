<?php

namespace App\Api\V1\Models;

class Provider extends ApiModel
{
    protected $table = 'providers';

    protected $fillable = [
        'name',
        'url'
    ];

}