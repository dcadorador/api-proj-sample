<?php

namespace App\Api\V1\Models;

class ApiResponse extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected $table = 'api_responses';

    protected static $translatable = ['message'];

    protected $fillable = [
        'code',
        'message'
    ];

    public static function findByCode($code)
    {
        return static::where('code',$code)->first();
    }
}