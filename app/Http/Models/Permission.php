<?php

namespace App\Api\V1\Models;

class Permission extends ApiModel
{

    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    public function roles() 
    {
        return $this->belongsToMany($this->ns.'\Role');
    }

}