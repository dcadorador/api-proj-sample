<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\ExternalOrder;


class ExternalOrderTransformer extends ApiTransformer
{

    public function transform(ExternalOrder $model)
    {
        $relationships = [];

        $data = [
            'concept-id' => $model->concept_id,
            'location-id' => $model->location_id,
            'order-hid' => $model->order_hid,
            'reference' => $model->reference,
            'order-time' => $model->order_time,
            'promised-time' => $model->promised_time,
            'total' => $model->total,
            'customer-hid' => $model->customer_hid,
            'customer' => $model->customer,
            'customer-phone' => $model->customer_phone,
            'delivery-hid' => $model->delivery_hid,
            'delivery-address' => $model->delivery_address,
            'delivery-address-longitude' => $model->delivery_address_longitude,
            'delivery-address-latitude' => $model->delivery_address_latitude,
            'payment-hid' => $model->payment_hid,
            'payment-amount' => $model->payment_amount,
            'payment-method' => $model->payment_method,
            'payment-date' => $model->payment_date
        ];

        return $this->transformAll($model, $relationships, $data);
    }
}