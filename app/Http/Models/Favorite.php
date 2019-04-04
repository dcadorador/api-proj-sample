<?php

namespace App\Api\V1\Models;

class Favorite extends ApiModel
{
    protected $table = 'customer_favorites';

    protected $fillable = [
        'customer_id',
        'item_id',
        'price',
        'image_uri',
        'item_ingredients_id'
    ];

    public function customer()
    {
        return $this->belongsTo($this->ns.'\Customer');
    }

    public function modifiers()
    {
        return $this->hasMany($this->ns.'\FavoriteItemModifier','favorite_item_id');
    }

    public function ingredients()
    {
        return $this->hasMany($this->ns.'\FavoriteItemIngredient','favorite_item_id');
    }

    public function item()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function getUriAttribute()
    {
        return $this->customer->uri . '/favorites/' . $this->id;
    }
}