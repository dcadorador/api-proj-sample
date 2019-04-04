<?php

namespace App\Api\V1\Models;

class SocialAuthentication extends ApiModel
{
    protected $table = 'social_authentication';

    protected $fillable = [
        'api_subscriber_id',
        'provider_user_id',
        'provider'
    ];



}