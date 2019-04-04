<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\OrderOrderStatus;
use App\Api\V1\Models\Concept;
use Illuminate\Support\Facades\Request;

class OrderStatusTransformer extends ApiTransformer
{

    public function transform(OrderOrderStatus $model)
    {
        $concept = Request::header('Solo-Concept');
        $concept = Concept::find($concept) ? Concept::find($concept) : null;
        $order = $model->order()->first();
        $description = $concept ? (
            $order->type == 'deliver' ? $concept->orderStatus()
                ->where('order_status_id',$model->order_status_id)
                ->first()
                ->delivery_description :
                $concept->orderStatus()
                    ->where('order_status_id',$model->order_status_id)
                    ->first()
                    ->pickup_description
        ) : (
            $order->type == 'deliver' ?
                $model->orderStatus->delivery_description :
                $model->orderStatus->pickup_description
        );

        $data = [
            'sequence' => $model->orderStatus->sequence,
            'code' => $model->orderStatus->code,
            //'description' => $order->type == 'deliver' ? $model->orderStatus->delivery_description : $model->orderStatus->pickup_description,
            'description' => $description,
            'changed-at' => $this->formatTimestamp($model->created_at)
        ];

        return $this->transformAll($model, [], $data);
	}
}