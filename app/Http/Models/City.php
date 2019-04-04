<?php

namespace App\Api\V1\Models;

class City extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    protected $table = 'cities';

    public function locations()
    {
        return $this->hasMany($this->ns.'\Location','city_id');
    }

    public function getUriAttribute() {
        return $this->getBaseUri() . '/cities/' . $this->id;
    }

}