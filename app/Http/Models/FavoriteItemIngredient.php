<?php

namespace App\Api\V1\Models;

class FavoriteItemIngredient extends ApiModel
{
    protected $table = 'favorite_item_ingredients';

    protected $fillable = [
        'favorite_item_id',
        'ingredient_id',
        'quantity',
        'price'
    ];

    public function favorite()
    {
        return $this->belongsTo($this->ns.'\Favorite','favorite_item_id');
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