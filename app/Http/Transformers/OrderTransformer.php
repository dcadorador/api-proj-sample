<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Order;
use App\Api\V1\Models\OrderStatus;
use App\Api\V1\Helpers\AuthHelper;
use Illuminate\Support\Facades\Request;


class OrderTransformer extends ApiTransformer
{

    /**
     * List of resources possible to include
     *
     * @var array
     */

    protected $availableIncludes = [
        'items',
        'employees',
        'customerAddress',
        'location'
    ];

    public function transform(Order $model)
    {
        $lang = Request::header('Accept-Language');
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

        $concept = $model->concept()->first();

        if($lang == 'ar-sa'){
            if($model->type == 'deliver' or $model->type == 'delivery') {
                $type = 'ايصال';
            } elseif ($model->type == 'pickup') {
                $type = 'امسك';
            } elseif($model->type == 'eat-in') {
                $type = 'أكلت داخل';
            } else {
                $type = 'سفري';
            }
        } else {
            $type = $model->type;
        }

        $data = [
            'reference' => $model->reference,
            'code' => $model->code,
            'source' => $model->source,
            'type' => $type,
            'promised-time' => $this->formatTimestamp($model->promised_time),
            'scheduled-time' => $model->scheduled_time ? $this->formatTimestamp($model->scheduled_time) : null,
            'subtotal' => $model->subtotal,
            'delivery-charge' => $model->delivery_charge,
            'discount' => $model->discount,
            'total' => $model->total,
            'vat-amount' => $model->vat_amount,
            'coupon-code' => $model->coupon_code,
            'payment-method' => $model->payment_type,
            'vat-rate' => $concept->vat_rate,
            'order-pos-response' => $model->order_pos_response,
            'notes' => $model->notes,
            'item-count' => $model->orderItems->count(),
            'favorite' => false
        ];

        if ($subscriber = AuthHelper::getAuthenticatedUser()) {
            $customer = $subscriber->userable()->first();
            $data['favorite'] = Order::where('id',$model->id)
                    ->where('customer_id',$customer->id)
                    ->where('customer_favorite',1)
                    ->count() > 0;
        }

        $response = $this->transformAll($model, $relationships, $data);

        if ($orderStatus && sizeof($orderStatus)>0) {
            $concept = $model->concept;
            $current_status = [
                'sequence' => $orderStatus[0]->orderStatus->sequence,
                'code' => $orderStatus[0]->orderStatus->code,
                'type' => $orderStatus[0]->orderStatus->type,
                'description' => $model->type == 'deliver' ?
                    $concept->orderStatus()->where('order_status_id',$orderStatus[0]->orderStatus->id)->first()->delivery_description :
                    $concept->orderStatus()->where('order_status_id',$orderStatus[0]->orderStatus->id)->first()->pickup_description,
                'changed-at' => $this->formatTimestamp($orderStatus[0]->created_at)
            ];
            $response = array_add($response, 'current-status', $current_status);
        }

        return $response;
	}

    /**
     * Include Customer
     *
     * @return League\Fractal\ItemResource
     */

    public function includeItems(Order $model)
    {
        $order_items = $model->orderItems;

        return $this->collection($order_items, new OrderItemTransformer,'order-item');
    }

    /**
     * Include Driver
     *
     * @return League\Fractal\ItemResource
     */

    public function includeEmployees(Order $model)
    {
        $employees = $model->employees;

        return $this->collection($employees, new EmployeeTransformer, 'employee');
    }

    /**
     * Include Address
     *
     * @return League\Fractal\ItemResource
     */

    public function includeCustomerAddress(Order $model)
    {
        $address = $model->customerAddress;
        if ($address) {
            return $this->item($address, new CustomerAddressTransformer, 'customer-address');
        }
    }

    public function includeLocation(Order $model)
    {
        $location = $model->location;
        if($location) {
            return $this->item($location, new LocationTransformer, 'location');
        }
    }

}