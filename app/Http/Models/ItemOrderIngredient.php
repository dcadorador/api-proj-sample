<?php

namespace App\Api\V1\Models;

class ItemOrderIngredient extends ApiModel
{
    protected $table = 'item_order_ingredient';

    protected $fillable = [
        'item_order_id',
        'ingredient_id',
        'quantity',
        'price',
        'item_ingredients_id'
    ];

    public function orderItem()
    {
        return $this->belongsTo($this->ns.'\ItemOrder','item_order_id');
    }

    public function ingredient()
    {
        return $this->belongsTo($this->ns.'\ItemIngredient','item_ingredients_id');
    }

    public function getUriAttribute()
    {
        return $this->ingredient->uri;
    }

}