<?php

namespace App\Api\V1\Models;

use Carbon\Carbon;

class Location extends ApiModel
{

    use \igaster\TranslateEloquent\TranslationTrait;

    protected static $translatable = ['name'];

    protected $fillable = [
        'code',
        'concept_id',
        'status',
        'name',
        'telephone',
        'email',
        'line1',
        'line2',
        'country',
        'lat',
        'long',
        'pos',
        'delivery_charge',
        'opening_hours',
        'promised_time_delta_delivery',
        'promised_time_delta_pickup',
        'delivery_enabled',
        'city_id'
    ];

    /**
     * Get the devices registered at the location.
     */
    public function devices()
    {
        return $this->hasMany($this->ns.'\Device');
    }

    public function openOrders()
    {
        return $this->hasMany($this->ns.'\Order');
    }

    public function employees()
    {
        return $this->belongsToMany($this->ns.'\Employee');
    }

    public function areas()
    {
        return $this->hasMany($this->ns.'\DeliveryArea');
    }

    public function concept()
    {
        return $this->belongsTo($this->ns.'\Concept');
    }

    public function items()
    {
        return $this->belongsToMany($this->ns. '\Item', 'item_location', 'location_id','item_id');
    }

    public function modifiers()
    {
        return $this->belongsToMany($this->ns. '\Modifier', 'location_modifiers', 'location_id','modifier_id');
    }

    public function city()
    {
        return $this->belongsTo($this->ns. '\City');
    }

    public function getIsOpenAttribute()
    {
        // added the timezone from concept setting
        $concept = $this->concept()->first();
        $default_timezone = $concept->default_timezone ? $concept->default_timezone : 'GMT+3';

        $openingHours = json_decode($this->opening_hours);
        if ($openingHours == null) {
            return true;
        }

        $dt = new Carbon();
        $dayOfWeek = $dt->dayOfWeek;
        foreach($openingHours as $dayConfig) {
            if ($dayConfig->day == $dayOfWeek) {
                break;
            }
        }

        if(
            ($dayConfig->open == '00:00' and $dayConfig->closed == '00:00') or
            ($dayConfig->closed == $dayConfig->open)
        ){
            return true;
        }

        $open = new Carbon();
        $closed = new Carbon();

        $REGEX = '/([0-9]{1,2}):([0-9]{2})/';
        if (preg_match($REGEX, $dayConfig->open, $openMatch)===1) {
            $open->setTime($openMatch[1], $openMatch[2]);
        }
        else {
            $open->setTime(0, 0);
        }

        if (preg_match($REGEX, $dayConfig->closed, $closedMatch)===1) {
            $closed->setTime($closedMatch[1], $closedMatch[2]);
        }
        else {
            $closed->setTime(0, 0);
        }

        if ($closed->hour < $open->hour) {
            $closed->addDay(1);
        }

        // handle the conversion
        $open = Carbon::createFromFormat('Y-m-d H:i:s', $open->toDateTimeString(), $default_timezone);
        $closed = Carbon::createFromFormat('Y-m-d H:i:s', $closed->toDateTimeString(), $default_timezone);

        $isOpen = $dt->setTimezone($default_timezone)->gte($open) && $dt->setTimezone($default_timezone)->lte($closed);
        return $isOpen;
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/locations/'. $this->id;
    }

    public function geofence()
    {
        return $this->hasMany($this->ns.'\Geofence');
    }

}