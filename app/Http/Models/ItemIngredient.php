<?php

namespace App\Api\V1\Models;

class ItemIngredient extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected $fillable = [
        'display_order',
        'quantity',
        'minimum_quantity',
        'maximum_quantity',
        'enabled'
    ];

    public function item()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function ingredient()
    {
        return $this->belongsTo($this->ns.'\Ingredient');
    }

    public function getUriAttribute() 
    {
        return $this->item->uri . '/item-ingredients/' . $this->id;
    }
}