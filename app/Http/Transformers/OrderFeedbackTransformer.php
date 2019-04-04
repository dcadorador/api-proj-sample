<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Bearing;
use App\Api\V1\Models\OrderFeedback;


class OrderFeedbackTransformer extends ApiTransformer
{

    public function transform(OrderFeedback $model)
    {

        $relationships = [
            'order' => $model->employee->uri,
        ];

        $data = [
            'rating' => $model->rating,
            'order' => $model->order,
            'subject' => $model->subject,
            'body' => $model->body,
            'image' => $model->image,
            'topic' => $model->topic
        ];

        return $this->transformAll($model, $relationships, $data);
    }
}