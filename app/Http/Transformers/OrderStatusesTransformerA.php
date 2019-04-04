<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\OrderStatus;
use App\Api\V1\Models\Concept;
use Illuminate\Support\Facades\Request;

class OrderStatusesTransformerA extends ApiTransformer
{
    public function transform(OrderStatus $model)
    {
        $concept = Request::header('Solo-Concept');
        $concept = Concept::find($concept) ? Concept::find($concept) : null;

        $data = [
            'sequence' => $model->sequence,
            'code' => $model->code,
            'type' => $model->type,
            'description' => $concept ?
                $concept->orderStatus()
                ->where('order_status_id',$model->id)
                ->first()
                ->delivery_description : $model->delivery_description
        ];

        return $this->transformAll($model, [], $data);
    }
}