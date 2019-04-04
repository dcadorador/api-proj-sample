<?php

namespace App\Api\V1\Models;

class ExternalOrderOrderStatus extends ApiModel
{

    protected $table = 'external_order_order_status';

    protected $fillable = [
        'external_order_id',
        'external_order_status_id'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Indicates if the model should force an auto-incrementeing id.
     *
     * @var bool
     */
    public $incrementing = true;

    public function externalOrder() {
        return $this->belongsTo($this->ns.'\ExternalOrder');
    }

    public function orderStatus() {
        return $this->belongsTo($this->ns.'\ExternalOrderStatus');
    }

}