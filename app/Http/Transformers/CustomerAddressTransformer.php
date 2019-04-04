<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\CustomerAddress;
use App\Api\V1\Models\DeliveryArea;


class CustomerAddressTransformer extends ApiTransformer
{

    public function transform(CustomerAddress $model)
    {

        $relationships = [
            'customer' => $model->customer->uri
        ];

        if ($model->deliveryArea) {
            $location = $model->deliveryArea->location;
            $relationships = array_add($relationships, 'delivery-area', $location->uri.'/delivery-areas/'.$model->deliveryArea->id);
        }

        return $this->transformAll($model, $relationships, [
            'status' => $model->status,
            'label' => $model->label,
            'country' => $model->country,
            'postal-code' => $model->postal_code,
            'state' => $model->state,
            'city' => $model->city,
            'line1' => $model->line1,
            'line2' => $model->line2,
            'lat' => $model->lat,
            'long' => $model->long,
            'telephone' => $model->telephone,
            'instructions' => $model->instructions,
            'photo-uri' => $model->photo_uri,
            'delivery-area-id' => $model->delivery_area_id
        ]);

    }

}