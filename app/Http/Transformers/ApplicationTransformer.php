<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Application;


class ApplicationTransformer extends ApiTransformer
{

    public function transform(Application $model)
    {

        $relationships = [
            'concept' => $model->concept->uri,
        ];

        return $this->transformAll($model, $relationships, [
            'label' => $model->label,
            'google-arn' => $model->google_arn,
            'apple-arn' => $model->apple_arn,
            'web-arn' => $model->web_arn,
            'broadcast-topic-arn' => $model->broadcast_topic_arn
        ]);
    }
}