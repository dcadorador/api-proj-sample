<?php

namespace App\Api\V1\Models;

class Category extends ApiModel
{

    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name', 'description'];

    protected $fillable = [
        'code',
        'menu_id',
        'display_order',
        'name',
        'description',
        'image_uri',
        'enabled'
    ];


    public function menu()
    {
        return $this->belongsTo($this->ns.'\Menu');
    }

    public function items() 
    {
        return $this->hasMany($this->ns.'\Item');
    }

    public function getUriAttribute()
    {
        return $this->menu->uri . '/categories/' . $this->id;
    }

}