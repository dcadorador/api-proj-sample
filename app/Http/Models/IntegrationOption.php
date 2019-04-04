<?php

namespace App\Api\V1\Models;

class IntegrationOption extends ApiModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'integration_id', 'option_key', 'option_value',
    ];

}
