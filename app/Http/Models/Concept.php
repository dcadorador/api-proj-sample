<?php

namespace App\Api\V1\Models;

class Concept extends ApiModel
{
    protected $fillable = [
        'client_id',
        'label',
        'country',
        'dialing_code',
        'currency_code',
        'currency_symbol',
        'logo_uri',
        'default_opening_hours',
        'default_menu_id',
        'default_pos',
        'default_delivery_charge',
        'default_promised_time_delta_delivery',
        'default_promised_time_delta_pickup',
        'default_minimum_order_amount',
        'default_driver_location_ttl',
        'order_cancellation_time',
        'order_cancellation_max_status',
        'order_price_calculation',
        'default_schedule_delivery_time',
        'vat_type',
        'vat_rate',
        'default_payfort_config',
        'default_order_status_card',
        'default_order_status_cash',
        'minimum_order_amount_delivery',
        'minimum_order_amount_pickup'
    ];

    public function client()
    {
        return $this->belongsTo($this->ns.'\Client');
    }

    public function menus()
    {
        return $this->hasMany($this->ns.'\Menu');
    }

    public function customers()
    {
        return $this->belongsToMany($this->ns.'\Customer','concept_customers');
    }

    public function sliders()
    {
        return $this->belongsToMany($this->ns.'\Slider');
    }

    public function getUriAttribute() 
    {
        return $this->getBaseUri() . '/concepts/' . $this->id;
    }

    public function employees()
    {
        return $this->belongsToMany($this->ns.'\Employee','concept_employee','concept_id','employee_id');
    }

    public function applications()
    {
        return $this->hasMany($this->ns.'\Application');
    }

    public function topics()
    {
        return $this->hasMany($this->ns . '\Topic');
    }

    public function orderStatus()
    {
        return $this->hasMany($this->ns . '\ConceptOrderStatus','concept_id');
    }
}