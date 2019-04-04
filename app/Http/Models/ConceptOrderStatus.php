<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class ConceptOrderStatus extends ApiModel
{

    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['delivery_description','pickup_description'];

    protected $table = 'concept_order_statuses';

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

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function orderStatus() {
        return $this->belongsTo($this->ns.'\OrderStatus');
    }

}