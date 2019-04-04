<?php

namespace App\Api\V1\Models;

class ModifierGroup extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function modifiers() 
    {
        return $this->hasMany($this->ns.'\Modifier');
    }

    public function items()
    {
        return $this->belongsToMany($this->ns.'\Item');
    }

    public function getUriAttribute() 
    {
        return $this->getBaseUri() . '/modifier-groups/' . $this->id;
    }

}