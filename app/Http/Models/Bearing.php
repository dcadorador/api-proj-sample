<?php

namespace App\Api\V1\Models;


class Bearing extends ApiModel
{

    protected $table = 'employee_bearings';

    protected $fillable = ['employee_id', 'lat', 'long'];

    public function employee()
    {
        return $this->belongsTo($this->ns.'\Employee');
    }

    public function getUriAttribute()
    {
        return $this->employee->uri . '/bearings/' . $this->id;
    }
}