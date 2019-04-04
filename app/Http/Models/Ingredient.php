<?php

namespace App\Api\V1\Models;

class Ingredient extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    protected $fillable = [
        'concept_id',
        'code',
        'name',
        'display_order',
        'image_uri',
        'price',
        'calorie_count',
        'enabled'
    ];

    public function item()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function favoriteItemIngredient()
    {
        return $this->hasMany($this->ns.'\FavoriteItemIngredient');
    }

    public function getUriAttribute() 
    {
        return $this->getBaseUri() . '/ingredients/' . $this->id;
    }
}