<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Order;
use App\Api\V1\Models\OrderStatus;


class OrderDetailTransformer extends ApiTransformer
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    /*
        protected $availableIncludes = [
            'customer',
            'driver'
        ];
    */


    public function transform(Order $model)
    {

        $orderStatus = $model->currentStatus;

        $relationships = [
            'items' => $model->uri.'/items',
            'status-history' => $model->uri.'/statuses',
            'payments' => $model->uri.'/payments'
        ];

        if ($model->location) {
            $relationships = array_add($relationships, 'location', $model->location->uri);
        }
        if ($model->customer) {
            $relationships = array_add($relationships, 'customer', $model->customer->uri);
        }

        $response = $this->transformAll($model, $relationships, [
            'reference' => $model->reference,
            'code' => $model->code,
            'source' => $model->source,
            'type' => $model->type,
            'promised-time' => $this->formatTimestamp($model->promised_time),
            'subtotal' => $model->subtotal,
            'total' => $model->total,
            'discount' => $model->discount,
            'coupon-code' => $model->coupon_code,
            'payment-type' => $model->payment_type

        ]);

        if ($orderStatus && sizeof($orderStatus)>0) {
            $current_status = [
                'sequence' => $orderStatus[0]->orderStatus->sequence,
                'code' => $orderStatus[0]->orderStatus->code,
                'description' => $orderStatus[0]->orderStatus->description,
                'changed-at' => $this->formatTimestamp($orderStatus[0]->created_at)
            ];
            $response = array_add($response, 'current-status', $current_status);
        }

        $order_items = $model->orderItem()->get();
        $transform = new OrderItemTransformer();
        $items  = $transform->transformCollection($order_items->all());
        $response = array_add($response, 'items', $items);

        return $response;
    }

    /**
     * Include Customer
     *
     * @return League\Fractal\ItemResource
     */
    /*
        public function includeCustomer(Order $model)
        {
            $customer = $model->customer;

            return $this->item($customer, new CustomerTransformer);
        }
    */
    /**
     * Include Driver
     *
     * @return League\Fractal\ItemResource
     */
    /*
        public function includeDriver(Order $model)
        {
            $driver = $model->employee;

            return $this->item($driver, new EmployeeTransformer, ['key' => 'employee']);
        }
    */
}