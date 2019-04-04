<?php

namespace App\Api\V1\Models;

class Role extends ApiModel
{

    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    public function employees()
    {
        return $this->belongsToMany($this->ns.'\Employee');
    }

    public function permissions() 
    {
        return $this->belongsToMany($this->ns.'\Permission');
    }

    public function givePermissionTo(Permission $permission)
    {
        return $this->permissions()->save($permission);
    }

}