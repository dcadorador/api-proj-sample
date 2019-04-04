<?php

namespace App\Api\V1\Models;

class FavoriteItemModifier extends ApiModel
{
    protected $table = 'favorite_item_modifiers';

    protected $fillable = [
        'favorite_item_id',
        'modifier_id',
        'quantity',
        'price'
    ];

    public function favorite()
    {
        return $this->belongsTo($this->ns.'\Favorite','favorite_item_id');
    }

    public function modifier()
    {
        return $this->belongsTo($this->ns.'\Modifier');
    }

    public function getModifierGroupAttribute()
    {
        return $this->modifier->modifierGroup;
    }

}