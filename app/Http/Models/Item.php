<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait, SoftDeletes;

    protected static $translatable = ['name', 'description'];

    protected $fillable = [
        'code',
        'category_id',
        'display_order',
        'name',
        'description',
        'image_uri',
        'price',
        'in_stock',
        'calorie_count',
        'deleted_at',
        'enabled'
    ];

    public function category()
    {
        return $this->belongsTo($this->ns.'\Category');
    }

    public function modifierGroups()
    {
        return $this->belongsToMany($this->ns.'\ModifierGroup');
    }

    public function favorites()
    {
        return $this->hasMany($this->ns . '\Favorite','favorite_item_id');
    }

    public function ingredients()
    {
        return $this->hasMany($this->ns . '\ItemIngredient');
    }

    public function bundledItems() 
    {
        return $this->hasOne($this->ns . '\BundledItem', 'parent_item_id');
    }

    public function timedEvents()
    {
        return $this->belongsToMany($this->ns . '\TimedEvent','item_timed_events','item_id','timed_events_id');
    }

    public function getUriAttribute()
    {
        return $this->category->uri . '/items/' . $this->id;
    }

    public function locations()
    {
        return $this->belongsToMany($this->ns.'\Location', 'item_location', 'item_id','location_id')
            ->withTimestamps();
    }

}