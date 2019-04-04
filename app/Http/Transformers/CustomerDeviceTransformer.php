<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\CustomerDevice;


class CustomerDeviceTransformer extends ApiTransformer
{

    public function transform(CustomerDevice $model)
    {
        $relationships = [];

        if ($model->customer) {
            $relationships = array_add($relationships, 'customer', $model->customer->uri);
        }

        $data = [
            'application-id' => $model->application_id,
            'device-token' => $model->device_token,
            'device-id' => $model->device_id,
            'model' => $model->model,
            'endpoint-arn' => $model->endpoint_arn,
            'topic-subscription-arn' => $model->topic_subscription_arn,
        ];

        if ($model->token) {
            $data['token'] = $model->token;
        }

        return $this->transformAll($model, $relationships, $data);
    }
}