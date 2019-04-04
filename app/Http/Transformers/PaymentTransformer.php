<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Payment;

class PaymentTransformer extends ApiTransformer
{

    public function transform(Payment $model)
    {

        $relationships = [
            'order' => $model->order->uri
        ];

        $data = [
            'code' => $model->code,
            'order-id' => $model->order_id,
            'method' => $model->method,
            'amount' => $model->amount,
            'payment-reference-number' => $model->payment_reference_number,
            'cash-presented' => $model->cash_presented
        ];

        return $this->transformAll($model, $relationships, $data);
    }

}