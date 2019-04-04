<?php

namespace App\Api\V1\Models;

class Topic extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    protected $fillable = [
        'concept_id',
        'code',
        'name',
        'type'
    ];

    const TYPE = [
        1 => 'DELIVERY',
        2 => 'NON-DELIVERY'
    ];

    public function orders()
    {
        return $this->belongsToMany($this->ns.'\Order', 'order_topic')
            ->withTimestamps();
    }
}