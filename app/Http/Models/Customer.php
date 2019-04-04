<?php

namespace App\Api\V1\Models;

class Customer extends ApiModel
{
    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'status',
        'account_type',
        'sms_code',
        'customer_favorite',
        'provider_id'
    ];

    public function orders()
    {
        return $this->hasMany($this->ns.'\Order');
    }

    public function user()
    {
    	return $this->morphOne($this->ns . '\ApiSubscriber', 'userable');
    }

    public function addresses()
    {
        return $this->hasMany($this->ns . '\CustomerAddress');
    }

    public function devices()
    {
        return $this->hasMany($this->ns . '\CustomerDevice');
    }

    public function favorites()
    {
        return $this->hasMany($this->ns . '\Favorite');
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/customers/' . $this->id;
    }

    public function concepts()
    {
        return $this->belongsToMany($this->ns . '\Concept','concept_customers','customer_id','concept_id');
    }


    public function verified()
    {
        $this->status = 'verified';
        $this->sms_code = null;
        $this->save();
    }
}