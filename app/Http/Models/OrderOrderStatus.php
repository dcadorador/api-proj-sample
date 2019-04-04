<?php

namespace App\Api\V1\Models;

class OrderOrderStatus extends ApiModel
{

    use \igaster\TranslateEloquent\TranslationTrait;

    protected $table = 'order_order_status';

    protected $fillable = [
        'order_id',
        'order_status_id'
    ];

    public function order() {
        return $this->belongsTo($this->ns.'\Order');
    }

    public function orderStatus() {
        return $this->belongsTo($this->ns.'\OrderStatus');
    }

    public function getUriAttribute() {
        return $this->order->uri . '/statuses/' . $this->id;
    }

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

}