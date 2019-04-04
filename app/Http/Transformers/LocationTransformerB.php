<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Location;
use Carbon\Carbon;

class LocationTransformerB extends ApiTransformer
{

    protected $availableIncludes = [
        'city'
    ];

    public function transform(Location $model)
    {

        $relationships = [
            'devices' => $model->uri . '/devices',
            'delivery-areas' => $model->uri . '/delivery-areas',
            'geofences' => $model->uri . '/geofences'
        ];

        if ($model->city) {
            $relationships = array_add($relationships, 'city', $model->city->uri);
        }

        $concept = $model->concept;

        return $this->transformAll($model, $relationships, [
            'status' => $model->status,
            'name' => $model->name,
            'telephone' => $model->telephone,
            'email' => $model->email,
            'line1' => $model->line1,
            'line2' => $model->line2,
            'country' => $model->country,
            'lat' => $model->lat,
            'long' => $model->long,
            'pos' => $model->pos,
            'is-open' => $model->isOpen,
            'delivery-charge' => $model->delivery_charge,
            'promised-time-delta' => [
                'delivery' => $model->promised_time_delta_delivery,
                'pickup' => $model->promised_time_delta_pickup
            ],
            'delivery-enabled' => $model->delivery_enabled,
            'distance' => round($model->distance,0,PHP_ROUND_HALF_UP),
            'opening-hours' => json_decode($model->opening_hours),
            'current-datetime' => $concept ? Carbon::now()->setTimezone($concept->default_timezone)->toDateTimeString() : Carbon::now()->toDateTimeString()
        ]);

    }

    public function includeCity(Location $model)
    {
        $city = $model->city;

        return $this->item($city, new CityTransformer, 'city');
    }
}