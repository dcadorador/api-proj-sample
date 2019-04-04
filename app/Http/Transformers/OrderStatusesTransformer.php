<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\OrderStatus;
use App\Api\V1\Models\Concept;
use Illuminate\Support\Facades\Request;


class OrderStatusesTransformer extends ApiTransformer
{
    public function transform(OrderStatus $model)
    {
        $concept = Request::header('Solo-Concept');
        $concept = Concept::find($concept) ? Concept::find($concept) : null;

        $data = [
            'sequence' => $model->sequence,
            'code' => $model->code,
            'type' => $model->type,
            'delivery-description' => $concept ? $concept->orderStatus()->where('order_status_id',$model->id)->first()->delivery_description : $model->delivery_description,
            'pickup-description' => $concept ? $concept->orderStatus()->where('order_status_id',$model->id)->first()->pickup_description : $model->pickup_description
        ];

        return $this->transformAll($model, [], $data);
    }
}