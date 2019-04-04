<?php

namespace App\Api\V1\Models;

class TimedEvent extends ApiModel
{
    protected $table = 'timed_events';

    protected $fillable = [
        'concept_id',
        'label',
        'code',
        'is_active',
        'value',
        'from_date',
        'to_date',
        'event_times'
    ];

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function items()
    {
        return $this->belongsToMany($this->ns.'\Item','item_timed_events','timed_events_id','item_id');
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/timed-events/' . $this->id;
    }
}