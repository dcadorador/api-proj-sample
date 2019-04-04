<?php

namespace App\Api\V1\Models;

class Export extends ApiModel
{
    protected $table = 'exports';

    protected $fillable = [
        'concept_id',
        'type',
        'csv_uri'
    ];

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function getUriAttribute() {
        return $this->getBaseUri() . '/exports/' . $this->id;
    }

}