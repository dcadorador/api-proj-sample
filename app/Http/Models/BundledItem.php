<?php

namespace App\Api\V1\Models;

class BundledItem extends ApiModel
{
    protected $fillable = [
        'id',
        'parent_item_id',
        'primary_item_id',
    ];

    public function primaryItem()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function parentItem()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function bundledCategories()
    {
        return $this->hasMany($this->ns.'\BundledCategory');
    }

    public function getUriAttribute()
    {
        return $this->parentItem->uri . '/bundled-items/' . $this->id;
    }

}