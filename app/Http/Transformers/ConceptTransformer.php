<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Concept;
use App\Api\V1\Models\OrderStatus;
use Carbon\Carbon;

class ConceptTransformer extends ApiTransformer
{

    public function transform(Concept $model)
    {

        $relationships = [
            'client' => $model->client->uri
        ];

        $cash_order_status = OrderStatus::find($model->default_order_status_cash);
        $card_order_status = OrderStatus::find($model->default_order_status_card);

        $data = [
            'label' => $model->label,
            'country' => $model->country,
            'dialing-code' => $model->dialing_code,
            'currency-code' => $model->currency_code,
            'currency-symbol' => $model->currency_symbol,
            'logo-uri' => $model->logo_uri,
            'default-timezone' => $model->default_timezone,
            'default-delivery-charge' => $model->default_delivery_charge,
            'default-pos' => $model->default_pos,
            'default-promised-time-delta-delivery' => $model->default_promised_time_delta_delivery,
            'default-promised-time-delta-pickup' => $model->default_promised_time_delta_pickup,
            'default-minimum-order-amount' => $model->default_minimum_order_amount,
            'order-cancellation-time' => $model->order_cancellation_time,
            'order-cancellation-max-status' => $model->order_cancellation_max_status,
            'order-price-calculation' => $model->order_price_calculation,
            'default-schedule-delivery-time' => $model->default_schedule_delivery_time,
            'vat-rate' => $model->vat_rate,
            'vat-type' => $model->vat_type,
            'default-payfort-config' => $model->default_payfort_config,
            'default-order-status-cash' => $cash_order_status->code,
            'default-order-status-card' => $card_order_status->code,
            'minimum-order-amount-delivery' => $model->minimum_order_amount_delivery,
            'minimum-order-amount-pickup' => $model->minimum_order_amount_pickup,
            'current-datetime' => $model->default_timezone ? Carbon::now()->setTimezone($model->default_timezone)->toDateTimeString() : Carbon::now()->toDateTimeString()
        ];

        return $this->transformAll($model, $relationships, $data);

    }

}