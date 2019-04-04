<?php

namespace App\Api\V1\Models;

class ItemModifier extends ApiModel
{
    protected $table = 'item_order_modifier';

    protected $fillable = [
        'item_order_id',
        'item_id',
        'modifier_id',
        'quantity',
        'price'
    ];

    public function item()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function modifier()
    {
        return $this->belongsTo($this->ns.'\Modifier');
    }

    public function orderItem()
    {
        return $this->belongsTo($this->ns.'\ItemOrder','item_order_id');
    }

    public function getUriAttribute()
    {
        return $this->modifier->uri;
    }

}