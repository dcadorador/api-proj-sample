<?php

namespace App\Api\V1\Models;

class ItemModifierGroup extends ApiModel
{

    protected $table = 'item_modifier_group';

    protected $fillable = [
        'item_id',
        'modifier_group_id'
    ];

    public function item()
    {
        return $this->belongsTo($this->ns.'\Item');
    }

    public function modifierGroup()
    {
        return $this->belongsTo($this->ns.'\ModifierGroup');
    }

    public function modifiers()
    {
        return $this->belongsTo($this->ns.'\ModifierGroup');
    }


}