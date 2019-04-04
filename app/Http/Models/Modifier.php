<?php

namespace App\Api\V1\Models;

class Modifier extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    protected $fillable = [
        'code',
        'modifier_group_id',
        'name',
        'display_order',
        'image_uri',
        'price',
        'minimum',
        'maximum',
        'calorie_count',
        'enabled'

    ];

    public function modifierGroup()
    {
        return $this->belongsTo($this->ns.'\ModifierGroup');
    }

    public function favoriteItemModifier()
    {
        return $this->hasMany($this->ns.'\FavoriteItemModifier');
    }

    public function locations()
    {
        return $this->belongsToMany($this->ns.'\Location', 'location_modifiers', 'modifier_id','location_id')
            ->withTimestamps();
    }


    public function getUriAttribute() 
    {
        return $this->modifierGroup->uri . '/modifiers/' . $this->id;
    }
}