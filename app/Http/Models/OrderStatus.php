<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{

    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['delivery_description','pickup_description'];

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

    public function conceptOrderStatus()
    {
        return $this->hasMany($this->ns . '\ConceptOrderStatus','order_status_id');
    }

}