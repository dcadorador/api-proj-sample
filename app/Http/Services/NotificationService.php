<?php

namespace App\Api\V1\Services;

use App\Api\V1\Models\Employee;
use App\Api\V1\Models\Order;
use App\Api\V1\Transformers\EmployeeTransformer;
use App\Api\V1\Transformers\OrderTransformer;
use Ably\Laravel\AblyService;

Class NotificationService {

    const NOTIF_TYPE_DRIVER = 'driver';
    const NOTIF_TYPE_ORDER = 'order';
    const NOTIF_TYPE_EMPLOYEE = 'employee';

    const CODE_NEW_ORDER = 'sc_order_new';
    const CODE_ORDER_CANCELLED = 'sc_ordered_canceled';
    const CODE_ORDER_STATUS = 'sc_order_status';
    const CODE_DELIVERED_ORDER = 'sc_order_delivered';
    const CODE_DRIVER_LOCATION = 'sc_driver_location';

    static $codeMessage = [
        'sc_order_new' => 'New order',
        'sc_ordered_canceled' => 'New order cancelled',
        'sc_order_delivered' => 'Order Delivered',
        'sc_driver_location' => 'Driver Location',
        'sc_order_status' => 'Order Status'
    ];

    public function triggerNewOrder($order)
    {
        $ably = new AblyService;
        $order_transformer = new OrderTransformer();
        $order_data = $order_transformer->transform($order);
        $customer = $order->customer()->first();
        $address = $order->customerAddress()->first();
        $driver = $order->driver()->first();

        $driver_array = $driver ? [
            'first-name' => $driver->first_name,
            'last-name' => $customer->last_name,
        ] : null;

        $data_add = $address ? [
            'id' => $address->id,
            'address' => $address->line1,
            'lat' => $address->lat,
            'long' => $address->long,
            'telephone' => $address->telephone,
            'instructions' => $address->instructions
        ] : null;

        $data = [
            'order' => $order_data,
            'customer' => [
                'id' => $customer->id,
                'first-name' => $customer->first_name,
                'last-name' => $customer->last_name,
                'address' => $data_add
            ],
            'driver' => $driver_array

        ];


        $ably->channel('solo-'.$order->concept_id.'-order')->publish('Solo\\New\\Order', $data);
        $ably->channel('solo-'.$order->concept_id.'-order-'.$order->location_id)->publish('Solo\\New\\Order', $data);
        // send all notifications to the location employees
        $location = $order->location()->first();

        // if location if present send all notifications to the employees
        if($location) {
            $employees = $location->employees()->get();
            foreach($employees as $employee) {
                //$ably->channel('solo-orders-new-'.'1')->publish('Solo\\New\\Order', $data);
                if(!$employee->hasRole('driver')) {
                    $ably->channel('solo-'.$order->concept_id.'-order-emp-'.$employee->id)->publish('Solo\\New\\Order', $data);
                }
            }
        }

        return null;
    }

    public function triggerStatusOrder($order)
    {
        $ably = new AblyService;
        $order_transformer = new OrderTransformer();
        $order_data = $order_transformer->transform($order);
        $customer = $order->customer()->first();
        $address = $order->customerAddress()->first();
        $driver = $order->driver()->first();

        $driver_array = $driver ? [
            'first-name' => $driver->first_name,
            'last-name' => $customer->last_name,
        ] : null;

        $data_add = $address ? [
            'id' => $address->id,
            'address' => $address->line1,
            'lat' => $address->lat,
            'long' => $address->long,
            'telephone' => $address->telephone,
            'instructions' => $address->instructions
        ] : null;

        $data = [
            'order' => $order_data,
            'customer' => [
                'id' => $customer->id,
                'first-name' => $customer->first_name,
                'last-name' => $customer->last_name,
                'address' => $data_add
            ],
            'driver' => $driver_array
        ];

        $ably->channel('solo-'.$order->concept_id.'-order-status')->publish('Solo\\Order\\Status', $data);
        //$ably->channel('solo-'.$order->concept_id.'-order-status-'.$order->location_id)->publish('Solo\\Order\\Status', $data);
        $ably->channel('solo-'.$order->concept_id.'-order-status-'.$order->id)->publish('Solo\\Order\\Status', $data);
        if($driver) {
            $ably->channel('solo-'.$order->concept_id.'-order-status-user-'.$driver->id)->publish('Solo\\Order\\Status', $data);
            //$ably->channel('solo-'.$order->concept_id.'-order-'.$order->id.'-emp-'.$driver->id)->publish('Solo\\Order\\Status', $data);
        }
        // send all notifications to the location employees
        $location = $order->location()->first();

        // if location if present send all notifications to the employees
        if($location) {
            $employees = $location->employees()->get();
            foreach($employees as $employee) {
                //$ably->channel('solo-order-status-'.'1')->publish('Solo\\Order\\Status', $data);
                $ably->channel('solo-'.$order->concept_id.'-order-status-user-'.$employee->id)->publish('Solo\\Order\\Status', $data);
            }
        }
        // fire an event for the customer and new order
        //$ably->channel('solo-order-status-'.'2')->publish('Solo\\Order\\Status', $data);
        //$ably->channel('solo-user-order-status-'.$customer->id)->publish('Solo\\Order\\Status', $data);
        return null;
    }

    public function triggerDriverBearings($employee,$bearing,$order)
    {
        $ably = new AblyService;
        $employee_transformer = new EmployeeTransformer();
        $emp_data = $employee_transformer->transform($employee);
        $order_transformer = new OrderTransformer();
        $order_data = $order_transformer->transform($order);

        $data = [
            'employee' => $emp_data,
            'bearings' => [
                'lat' => $bearing->lat,
                'long' => $bearing->long,
                'created-at' => $bearing->created_at->format('Y-m-d\TH:i:s'),
            ],
            'order' => $order_data
        ];
        app('log')->debug('SENDING ABLY NOTIFICATIONS');
        $ably->channel('solo-'.$order->concept_id.'-order-track')->publish('Solo\\Order\\Tracking', $data);
        $ably->channel('solo-'.$order->concept_id.'-order-track-'.$order->id)->publish('Solo\\Order\\Tracking', $data);
        // if location if present send all notifications to the employees
        /*if($locations) {
            foreach($locations as $location) {
                $loc_employees = $location->employees()->get();
                foreach($loc_employees as $loc_employee) {
                    $ably->channel('solo-driver-locations')->publish('Solo\\Driver\\Location', $data);
                    $ably->channel('solo-driver-locations-'.'1')->publish('Solo\\Driver\\Location', $data);
                    //$ably->channel('solo-driver-locations-5')->publish('Solo\\Driver\\Location', $data);
                    //$ably->channel('solo-driver-locations-'.'1')->publish('Solo\\Driver\\Location', $data);
                    $ably->channel('solo-driver-locations-'.$loc_employee->id)->publish('Solo\\Driver\\Location', $data);
                }
            }
        }

        $employee_order = app('db')->table('employee_order')->where('employee_id',$employee->id)->orderBy('created_at','DESC')->take(1)->first();
        $order = Order::where('id',$employee_order->order_id)->first();

        if($employee_order) {
            $customer = $order->customer()->first();
            $ably->channel('solo-user-driver-locations-'.$customer->id)->publish('Solo\\Driver\\Location', $data);
        }*/
        return null;
    }

    public function triggerDriverOrder($order, $employee)
    {
        $ably = new AblyService;
        $order_transformer = new OrderTransformer();
        $order_data = $order_transformer->transform($order);
        $customer = $order->customer()->first();
        $address = $order->customerAddress()->first();

        $driver = $employee ? [
            'first-name' => $employee->first_name,
            'last-name' => $employee->last_name,
        ] : null;

        $data_add = $address ? [
            'id' => $address->id,
            'address' => $address->line1,
            'lat' => $address->lat,
            'long' => $address->long,
            'telephone' => $address->telephone,
            'instructions' => $address->instructions
        ] : null;

        $data = [
            'order' => $order_data,
            'customer' => [
                'id' => $customer->id,
                'first-name' => $customer->first_name,
                'last-name' => $customer->last_name,
                'address' => $data_add
            ],
            'driver' => $driver
        ];

        $ably->channel('solo-'.$order->concept_id.'-order-status')->publish('Solo\\Order\\Status', $data);
        $ably->channel('solo-'.$order->concept_id.'-order-status-'.$order->location_id)->publish('Solo\\Order\\Status', $data);
        if($employee){
            $ably->channel('solo-'.$order->concept_id.'-order-emp-'.$employee->id)->publish('Solo\\New\\Order', $data);
        }
        /*// send all notifications to the location employees
        $location = $order->location()->first();

        // if location if present send all notifications to the employees
        if($location) {
            $employees = $location->employees()->get();
            foreach($employees as $employee) {
                $ably->channel('solo-user-orders-driver-'.$employee->id)->publish('Solo\\Driver\\Order', $data);
            }
        }

        // fire an event for the customer and new order
        $ably->channel('solo-user-orders-driver-'.$customer->id)->publish('Solo\\Driver\\Order', $data);*/
        return null;
    }
}