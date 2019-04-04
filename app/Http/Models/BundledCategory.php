<?php

namespace App\Api\V1\Models;

class BundledCategory extends ApiModel
{
    protected $fillable = [
        'id',
        'bundled_item_id',
        'category_id',
        'default_item_id',
    ];

    public function bundledItem()
    {
        return $this->belongsTo($this->ns.'\BundledItem');
    }

    public function defaultItem()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function category()
    {
        return $this->belongsTo($this->ns.'\Category');
    }

    public function getUriAttribute()
    {
        return $this->bundledItem->uri . '/bundled-categories/' . $this->id;
    }

}