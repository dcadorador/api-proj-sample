<?php

namespace App\Api\V1\Models;

class ExternalOrderStatus extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['delivery_description','pickup_description'];

    protected $table = 'external_order_statuses';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the model should force an auto-incrementeing id.
     *
     * @var bool
     */
    public $incrementing = true;
}