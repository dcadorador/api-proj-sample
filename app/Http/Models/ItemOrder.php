<?php

namespace App\Api\V1\Models;

class ItemOrder extends ApiModel
{
    protected $fillable = [
        'item_id',
        'order_id',
        'quantity',
        'notes',
        'price',
        'discount'
    ];

    public function order()
    {
        return $this->belongsTo($this->ns.'\Order');
    }

    public function item()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function itemOrderIngredients()
    {
        return $this->hasMany($this->ns.'\ItemOrderIngredient','item_order_id');
    }

    public function itemOrderModifiers()
    {
        return $this->hasMany($this->ns.'\ItemModifier','item_order_id');
    }

    /**
     * TODO THIS IS ADDED TO REMOVE NAMING ERROR IN TRANSFORMER INCLUDES
     */
    public function modifiers()
    {
        return $this->hasMany($this->ns.'\ItemModifier','item_order_id');
    }

    /**
     * TODO THIS IS ADDED TO REMOVE NAMING ERROR IN TRANSFORMER INCLUDES
     */
    public function ingredients()
    {
        return $this->hasMany($this->ns.'\ItemOrderIngredient','item_order_id');
    }

    public function getUriAttribute()
    {
        return $this->item->uri;
    }

}