<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Customer;
use App\Api\V1\Models\Item;
use App\Api\V1\Models\Modifier;
use App\Api\V1\Models\ModifierGroup;
use App\Api\V1\Models\ItemIngredient;
use App\Api\V1\Models\ItemModifier;
use App\Api\V1\Models\ItemOrder;
use App\Api\V1\Models\Concept;
use App\Api\V1\Services\NotificationService;
use App\Api\V1\Transformers\ItemTransformer;
use App\Api\V1\Transformers\OrderStatusesTransformer;
use App\Api\V1\Transformers\OrderStatusesTransformerA;
use App\Api\V1\Transformers\OrderStatusesTransformerB;
use App\Api\V1\Transformers\OrderItemTransformer;
use App\Api\V1\Transformers\OrderItemTransformerB;
use Illuminate\Http\Request;
use App\Api\V1\Models\Integration;
use App\Api\V1\Models\Order;
use App\Api\V1\Models\OrderOrderStatus;
use App\Api\V1\Models\OrderStatus;
use App\Api\V1\Models\Location;
use App\Api\V1\Transformers\OrderTransformer;
use App\Api\V1\Transformers\OrderStatusTransformer;
use App\Api\V1\Services\PointLocationService;
use App\Api\V1\Models\Point;
use App\Api\V1\Models\ItemOrderIngredient;
use App\Api\V1\Services\IntegrationService;
use App\Api\V1\Services\FoodicsIntegrationService;   // mini-AAARRRGGGHHH
use App\Api\V1\Services\HamburginiMealService;  // major-AAAAARRRRRRGGGGGGHHHHHHHH
use App\Api\V1\Models\Payment;
use Carbon\Carbon;
use App\Api\V1\Models\ApiResponse;
use DB;
use JWTAuth;
use App\Jobs\NewOrderJob;
use App\Jobs\StatusOrderJob;
use App\Jobs\FailedOrder;
use App\Api\V1\Helpers\SupportMailer;
use App\Api\V1\Helpers\AuthHelper;
use App\Api\V1\Models\ExternalOrder;
use App\Api\V1\Models\ExternalOrderOrderStatus;

class OrderController extends ApiController
{
    public function show(Request $request, $order)
    {
        $concept = $this->getConcept($request);
        $order = Order::where('id',$order)
            ->where('concept_id',$concept->id)
            ->first();

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        return $this->response->item($order, new OrderTransformer(), ['key' => 'order']);
    }

    public function editOrder(Request $request, $order)
    {
        $concept = $this->getConcept($request);
        $order = Order::where('id',$order)
            ->where('concept_id',$concept->id)
            ->first();

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        if($request->has('promised-time')){
            app('log')->debug('promised-time: '.$request->input('promised-time'));
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('promised-time'))->toDateTimeString();
            $order->promised_time = $date;
        }

        $order->update();

        return $this->response->item($order, new OrderTransformer(), ['key' => 'order']);
    }

    public function index(Request $request)
    {

        app('log')->debug('IN ORDER CONTROLLER -> INDEX');

        app('log')->debug('REQUEST:'.json_encode($request->all()));
        $concept = $this->getConcept($request);
        $orientation = $request->input('sort','desc');

        $orders = Order::with(['statuses'])
            ->where('concept_id', $concept->id)
            ->orderBy('created_at', $orientation);

        $filter = $request->input('filter',[]);

        if(array_key_exists('status',$filter)){
            $status = trim($filter['status']);
            $order_ids = Order::from('orders as c')->join(DB::raw("(SELECT MAX(id) max_id, order_id FROM order_order_status GROUP BY order_id) as oos"), function($join){
                $join->on('oos.order_id','c.id');
                })
                ->join('order_order_status','order_order_status.id','=','oos.max_id')
                ->whereIn('order_order_status.order_status_id',function($query) use ($status){
                    $query->select('id')->from('order_statuses')
                        ->orWhere('order_statuses.code',$status)
                        ->orWhere('order_statuses.type',$status);
                })
                ->where('c.customer_favorite',0)
                ->where('c.concept_id', $concept->id)
                ->orderBy('c.created_at', $orientation)
                ->pluck('c.id');

            $orders->whereIn('id', $order_ids);
            /*$orders->whereIn('id', function($query) use ($status){
                $status = (string)$status;
                $query->select('order_id')->from('order_order_status')
                   ->whereIn('order_status_id',function($query) use ($status){
                          $query->select('id')->from('order_statuses')
                              ->orWhere('code',$status)
                              ->orWhere('type',$status);
                   });
            });*/
        }

        if(array_key_exists('code',$filter)){
            $code = $filter['code'];
            $orders->where('code','LIKE','%'.$code.'%');
        }

        if(array_key_exists('location',$filter)){
            $location_ids = $filter['location'];
            if(strpos($location_ids,',') !== false){
                $location_ids = explode(',',$location_ids);
            } else {
                $location_ids = array($location_ids);
            }
            $orders->whereIn('location_id',$location_ids);
        }

        if(array_key_exists('customer',$filter)){
            $customer = strtolower($filter['customer']);
            $customer_object = Customer::whereHas('concepts', function($query) use($concept) {
                $query->where('concept_id',$concept->id);
            });
            // check if there is a name / id
            if (strpos(trim($customer), ',')) {
                // customer name are separated by comma
                $_name = explode(',', $customer, 2);
                $customer_object->where(function($query) use ($_name) {
                        $query->orWhere('first_name', 'LIKE', '%' . $_name[0] . '%');
                        $query->orWhere('first_name', 'LIKE', '%' . $_name[1] . '%');
                        $query->orWhere('last_name', 'LIKE', '%' . $_name[0] . '%');
                        $query->orWhere('last_name', 'LIKE', '%' . $_name[1] . '%');
                });

            } elseif(strpos(trim($customer), ' ')) {
                // customer name are separated by space
                $_name = preg_split('/\s+/', $customer, -1, PREG_SPLIT_NO_EMPTY);
                $customer_object->where(function($query) use ($_name){
                    foreach ($_name as $value) {
                        $query->orWhere('first_name', 'like', "%{$value}%");
                    }
                    foreach ($_name as $value) {
                        $query->orWhere('last_name', 'like', "%{$value}%");
                    }
                });

            } else {
                // if there is only 1, check if parameter is string/id
                if(is_numeric($customer)){
                    $customer_object->where('id',$customer);
                } else {
                    $customer_object->where(function ($query) use ($customer) {
                            $query->orWhere('first_name', 'LIKE', '%' . $customer . '%');
                            $query->orWhere('last_name', 'LIKE', '%' . $customer . '%');
                    });
                }
            }

            $cust_id_list = $customer_object->pluck('id');
            $orders->whereIn('customer_id',$cust_id_list);
        }

        if(array_key_exists('employee',$filter)){
            $employee = $filter['employee'];
            $orders->whereIn('id',function($query) use ($employee) {
                $query->select('order_id')->from('employee_order')
                    ->where('employee_id',$employee);
            });
        }

        if(array_key_exists('mobile',$filter)){
            $mobile = $filter['mobile'];
            $mobile = ltrim($mobile,'0');
            $mobile = starts_with($mobile, '966') ? str_replace('966','',$mobile) : $mobile ;
            $customer_ids = $concept->customers()->where('mobile','LIKE','%'.$mobile.'%')->pluck('id');
            $orders->whereIn('customer_id',$customer_ids);
        }

        if(array_key_exists('email',$filter)){
            $email = strtolower($filter['email']);
            $customer_ids = $concept->customers()->where('email',$email)->pluck('id');
            $orders->whereIn('customer_id',$customer_ids);
        }

        if(array_key_exists('order',$filter)){
            $orders->where('id',trim($filter['order']));
        }

        if(array_key_exists('from',$filter) and array_key_exists('to',$filter)){
            $from = trim($filter['from']);
            $to = trim($filter['to']);
            $orders->where('created_at','>=',Carbon::parse($from)->format('Y-m-d').' 00:00:00')
                ->where('created_at','<=',Carbon::parse($to)->format('Y-m-d').' 23:59:59');
        }

        if(array_key_exists('employee',$filter)){
            $employee = strtolower($filter['employee']);
            $employee_obj = Employee::whereHas('concepts', function($query) use($concept) {
                $query->where('concept_id',$concept->id);
            })->whereHas('roles', function($query) {
                $query->where('role_id',3);
            });

            if (strpos(trim($employee), ',')) {
                // customer name are separated by comma
                $_name = explode(',', $employee, 2);
                $employee_obj->where(function($query) use ($_name) {
                    $query->orWhere('first_name', 'LIKE', '%' . $_name[0] . '%');
                    $query->orWhere('first_name', 'LIKE', '%' . $_name[1] . '%');
                    $query->orWhere('last_name', 'LIKE', '%' . $_name[0] . '%');
                    $query->orWhere('last_name', 'LIKE', '%' . $_name[1] . '%');
                });

            } elseif(strpos(trim($employee), ' ')) {
                // customer name are separated by space
                $_name = preg_split('/\s+/', $employee, -1, PREG_SPLIT_NO_EMPTY);
                $employee_obj->where(function($query) use ($_name){
                    foreach ($_name as $value) {
                        $query->orWhere('first_name', 'like', "%{$value}%");
                    }
                    foreach ($_name as $value) {
                        $query->orWhere('last_name', 'like', "%{$value}%");
                    }
                });

            } else {
                // if there is only 1, check if parameter is string/id
                $employee_obj->where(function ($query) use ($employee) {
                    $query->orWhere('first_name', 'LIKE', '%' . $employee . '%');
                    $query->orWhere('last_name', 'LIKE', '%' . $employee . '%');
                });
            }
            $emp_id_list = $employee_obj->pluck('id');
            $order_ids = app('db')->table('employee_order')
                ->whereIn('employee_id',$emp_id_list)
                ->where('function','driver')
                ->pluck('order_id');
            $orders->whereIn('id',$order_ids);
        }

        // get employee orders using token
        if($subscriber = AuthHelper::getAuthenticatedUser()){
            if($subscriber) {
                if($subscriber->userable_type == 'employee'){
                    $emp = $subscriber->userable()->first();
                    if($emp) {
                        if($emp->roles->contains('id',3)){
                            $orders->whereIn('id',function($query) use ($emp) {
                                $query->select('order_id')->from('employee_order')
                                    ->where('employee_id',$emp->id);
                            });
                        } elseif(
                                ($emp->roles->contains('id',5) or $emp->roles->contains('id',2) or $emp->roles->contains('id',4)) and
                                $concept->id != 10
                            ){
                                $orders->whereIn('location_id',function($query) use ($emp) {
                                    $query->select('location_id')->from('employee_location')
                                        ->where('employee_id',$emp->id);
                                });
                        } elseif(
                            ($emp->roles->contains('id',2) or $emp->roles->contains('id',4)) and
                            $concept->id == 10
                        ) {
                            $order_ids = Order::from('orders as c')->join(DB::raw("(SELECT MAX(id) max_id, order_id FROM order_order_status GROUP BY order_id) as oos"), function($join){
                                $join->on('oos.order_id','c.id');
                            })
                                ->join('order_order_status','order_order_status.id','=','oos.max_id')
                                ->whereIn('order_order_status.order_status_id',function($query){
                                    $query->select('id')->from('order_statuses')
                                        ->orWhere('order_statuses.type','open')
                                        ->orWhere('order_statuses.type','closed');
                                })
                                ->where('c.customer_favorite',0)
                                ->where('c.concept_id', $concept->id)
                                ->orderBy('c.created_at', 'DESC')
                                ->pluck('c.id');
                            $orders->whereIn('id', $order_ids);
                        }
                        /*if($concept->id == 10) {
                            if($emp->roles->contains('id',5)){
                                $orders->whereIn('location_id',function($query) use ($emp) {
                                    $query->select('location_id')->from('employee_location')
                                        ->where('employee_id',$emp->id);
                                });
                            } elseif($emp->roles->contains('id',2) || $emp->roles->contains('id',4) || $emp->roles->contains('id',5)) {
                                $orders->whereIn('location_id',function($query) use ($emp) {
                                    $query->select('location_id')->from('employee_location')
                                        ->where('employee_id',$emp->id);
                                });
                            }
                        } else {
                            if($emp->roles->contains('id',3)){
                                $orders->whereIn('id',function($query) use ($emp) {
                                    $query->select('order_id')->from('employee_order')
                                        ->where('employee_id',$emp->id);
                                });
                            } elseif($emp->roles->contains('id',2) || $emp->roles->contains('id',4) || $emp->roles->contains('id',5)) {
                                $orders->whereIn('location_id',function($query) use ($emp) {
                                    $query->select('location_id')->from('employee_location')
                                        ->where('employee_id',$emp->id);
                                });
                            }
                        }*/
                    }
                }
            }
        }

        $data = $orders->paginate($this->perPage)->appends(app('request')->except('page'));
        
        app('log')->debug('Number of orders: '.count($data));

        // return collection with paginator
        return $this->response->paginator($data, new OrderTransformer, ['key' => 'order']);
    }

    public function store(Request $request)
    {
        $json = json_encode($request->all());
        app('log')->debug('INCOMING ORDER REQUEST:'.$json);
        app('log')->debug('INCOMING ORDER REQUEST HEADER:'.json_encode($request->headers->all()));
        $d = json_decode(json_encode($request->all()),true);

        // log in the logfile
        app('log')->info(print_r($d,true));

        // get the concept from the request
        $concept_id = $this->getConcept($request)->id;
        $concept = $this->getConcept($request);

        // payment type
        $payment = $request->json('payment',null);

        $address = $request->json('address',null);
        $cust = $request->json('customer');
        $type = $request->json('type');

        // check if customer exists
        $customer = Customer::where('id',$cust)
            ->first();

        if(!$customer){
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        /*
        $pay_type = $payment ? 'cash' : 'card';

        // check if customer has ordered in last 5 minutes
        app('log')->info('Checking for duplicate order...');
        $check =  Order::where('customer_id', $customer->id)
                ->where('concept_id', $concept->id)
                ->whereRaw('orders.created_at > (now() - interval ? minute)', 2)
                ->orderBy('created_at', 'DESC')
                ->first();
        if ($check and strtolower($check->payment_type) == $pay_type and $check->total == $request->json('total')) {
            app('log')->info('*** DUPLICATE ORDER ***.  Returning Previous order in response: '.$check->id);
            return $this->response->item($check, new OrderTransformer, ['key' => 'check']);
        }
        */

        // if address is not null then retrieve the nearest store.
        if($address || $address != '' ) {

            // get the customer address
            $customer_address = $customer->addresses->where('id', $address)
                ->first();

            // return error if address does not exist
            if(!$customer_address) {
                return response()->json(['error' => [
                    $this->responseArray(1006,404)
                ]], 404);
            }

            $lat = $customer_address->lat;
            $long = $customer_address->long;

            // get the nearest location
            $locations = $this->locationsLookup($concept_id,$lat,$long);

            // return error if the location cannot deliver
            if(!$locations){
                return response()->json(['error' => [
                    $this->responseArray(1011,400)
                ]], 400);
            }

            // set location id to the order
            $location = $locations->id;
        } else {
            $location = $request->json('location');
        }

        // check if location/branch exists
        $branch = Location::where('id',$location)
            ->first();

        if(!$branch) {
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
        }

        if(
            (
                strtolower(trim($type) == 'deliver') or strtolower(trim($type) == 'delivery')
            ) and $branch->delivery_enabled == 0
        ) {
            return response()->json(['error' => [
                $this->responseArray(1058,400)
            ]], 400);
        }

        // add removal of default minimum order
        $coupon = $request->json('coupon-code',null);
        $total = $request->json('total');
        $discount = $request->json('discount', 0);

        // added this to check only if the order is delivery
        if(strtolower(trim($type) == 'delivery') or strtolower(trim($type) == 'deliver')) {
            if( empty($coupon) ) {
                if(
                    $total < $concept->minimum_order_amount_delivery and
                    (int) $discount == 0
                ) {
                    return response()->json(['error' => [
                        $this->responseArray(1026,400)
                    ]], 400);
                }
            }

            // added this for MUNCH
            //if($concept->id == 10) {
            //    if($total < 100) {
            //        return response()->json(['error' => [
            //            $this->responseArray(1026,400)
            //        ]], 400);
            //    }
            //}
        } else {
            if( empty($coupon) ) {
                if(
                    $total < $concept->minimum_order_amount_pickup and
                    (int) $discount == 0
                ) {
                    return response()->json(['error' => [
                        $this->responseArray(1026,400)
                    ]], 400);
                }
            }
        }

        /*if( empty($coupon) ) {
            if(strtolower(trim($type) != 'pickup') and (int)$total == 0)  {
                if(
                    $total < $concept->default_minimum_order_amount and
                    (int) $discount == 0
                ) {
                    return response()->json(['error' => [
                        $this->responseArray(1026,400)
                    ]], 400);
                }
            }
        }*/

        //if($concept_id == 5) {
        //    $location = 76;
        //}

        $scheduled_time = null;
        if($request->has('scheduled-time')){
            app('log')->debug('ORDER API - Scheduled-time: '.$request->json('scheduled-time'));
            if($request->server('HTTP_ACCEPT_LANGUAGE') == 'ar-sa') {
                $eng_num = array('0','1','2','3','4','5','6','7','8','9');
                $ar_num = array('٠','١','٢','٣','٤','٥','٦','٧','٨','٩');
                $schedule = $request->json('scheduled-time');
                $scheduled_time = str_replace($ar_num, $eng_num, $schedule);
                $scheduled_time = Carbon::parse($scheduled_time);
            } else {
                $scheduled_time = Carbon::parse($request->json('scheduled-time'));
            }

            $allowed_time = Carbon::now()->addMinutes($concept->default_schedule_delivery_time);

            if($scheduled_time->lte($allowed_time)){
                return response()->json(['error' => [
                    $this->responseArray(1052,400)
                ]], 400);
            }
        }

        // handled the delivery charge
        $delivery_charge = $request->json('delivery-charge',0);
        if(
            $delivery_charge == 0 and
            (strtolower(trim($type) == 'deliver') or strtolower(trim($type) == 'delivery'))
        ) {
            $delivery_charge = $concept->default_delivery_charge;
        }

        // create order object
        $order = new Order();
        $order->location_id = $location;
        $order->concept_id = $concept_id;
        $order->customer_address_id = $address;
        $order->source = $request->json('source','Android');
        $order->type = $type;
        $order->customer_id = $cust;
        $order->device_id = $request->json('device');
        $order->scheduled_time = $scheduled_time;
        $order->total = $total;
        $order->subtotal = $request->json('subtotal');
        $order->discount = $discount;
        $order->delivery_charge = $delivery_charge;
        $order->coupon_code = $coupon;
        $order->payment_type = 'card';
        $order->vat_amount = $request->json('vat-amount', 0);
        $order->is_posted = 0;
        $order->notes = $request->json('notes');
        $now = new \DateTime;

        // set branch delivery delta
        if ($type == 'pickup') {
            $delta = ($branch->promised_time_delta_pickup != null || $branch->promised_time_delta_pickup != '') ? $now->add(new \DateInterval('PT'.$branch->promised_time_delta_pickup.'M')) : $now->add(new \DateInterval('PT'.$concept->default_promised_time_delta_pickup.'M'));
        } else {
            $delta = ($branch->promised_time_delta_delivery != null || $branch->promised_time_delta_delivery != '') ? $now->add(new \DateInterval('PT'.$branch->promised_time_delta_delivery.'M')) : null ;
        }

        $order->promised_time = $scheduled_time ? $scheduled_time : $delta;

        if($concept_id == 8){
            if($scheduled_time) {
                $order->created_at = Carbon::now()->toDateTimeString();
                $order->promised_time = $scheduled_time;
            } else {
                $order->created_at = Carbon::now()->toDateTimeString();
                $order->promised_time = Carbon::now()->toDateTimeString();
            }
        }

        $order->save();

        // set payment object if present, which means cash
        if($payment) {
            // update the order if cash payment
            $order->payment_type = 'cash';
            $order->update();

            // create payment object
            $payment = (array)$payment;
            $order->payments()->save(new Payment([
                'method' => $payment['method'],
                'amount' => $payment['amount'],
                'cash_presented' => $payment['cash-presented'],
                'status' => 'pending' // todo check if this is correct
            ]));
        }

        $items = $request->json('items');

        // create each item entry for the order
        foreach ($items as $item) {

            // items
            $item = (object)$item;
            $newItemPrice = 0;

            $item_order = $order->orderItems()->save(new ItemOrder([
                'item_id' => $item->id,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'notes' => property_exists($item,'notes') ? $item->notes : null,
                'discount' => property_exists($item,'discount') ? $item->discount : 0
            ]));


            // added to check if the item belongs to a meal category
            $itemObj = $item_order->item()->first();
            $category = $itemObj->category()->first();

            // this should apply only in hamburgini for now
            if($concept_id == 1 and $category and strtolower($category->translate('en-us')->name) == 'meals') {
                $service = new HamburginiMealService();
                $price = $service->calculateMealPrice(json_encode($item));
                $newItemPrice = $price;
            }

            switch($concept->order_price_calculation) {

                // item based
                case('item-based'):

                    // always get the item price and add the modifiers for item-based
                    $newItemPrice = $item->price;

                    // add the item price and modifier prices
                    if(isset($item->modifiers) and count($item->modifiers) > 0) {
                        foreach($item->modifiers as $modifier) {
                            $modifier = (object)$modifier;
                            $item_order->itemOrderModifiers()->save(new ItemModifier([
                                'modifier_id' => $modifier->id,
                                'price' => $modifier->price,
                                'quantity' => $modifier->quantity
                            ]));

                            $newItemPrice += ($modifier->price * $modifier->quantity);
                        }
                    }

                    break;

                // modifier based
                case('modifier-based'):
                default:

                if(isset($item->modifiers) and count($item->modifiers) > 0) {
                    foreach($item->modifiers as $modifier) {
                        $modifier = (object)$modifier;
                        $item_order->itemOrderModifiers()->save(new ItemModifier([
                            'modifier_id' => $modifier->id,
                            'price' => $modifier->price,
                            'quantity' => $modifier->quantity
                        ]));

                        if($category and strtolower($category->translate('en-us')->name) != 'meals') {
                            $newItemPrice += ($modifier->price * $modifier->quantity);
                        }
                    }
                }

                // added if the item has no modifiers and is not a meal
                if(isset($item->modifiers)) {
                    if(count($item->modifiers) == 0) {
                        // get the price from item price if not a meal
                        if($category and strtolower($category->translate('en-us')->name) != 'meals') {
                            $newItemPrice += $item->price;
                        }
                    }
                }
                    break;
            }

            // ingredients
            if(isset($item->ingredients) and count($item->ingredients) > 0) {
                foreach($item->ingredients as $ingredient) {
                    $ingredient = (object)$ingredient;

                    // removed this not using the ingredient id anymore
                    //$itemIngredient = ItemIngredient::find($ingredient->id);
                    $item_order->itemOrderIngredients()->save(new ItemOrderIngredient([
                        'item_ingredients_id' => $ingredient->id,
                        'quantity' => $ingredient->quantity
                    ]));
                }
            }

            // modifiers/options
            /*if(isset($item->modifiers) and count($item->modifiers) > 0) {
                foreach($item->modifiers as $modifier) {
                    $modifier = (object)$modifier;
                    $item_order->itemOrderModifiers()->save(new ItemModifier([
                        'modifier_id' => $modifier->id,
                        'price' => $modifier->price,
                        'quantity' => $modifier->quantity
                    ]));

                    if($category and strtolower($category->translate('en-us')->name) != 'meals') {
                        $newItemPrice += ($modifier->price * $modifier->quantity);
                    }
               }
            }

            // added if the item has no modifiers and is not a meal
            if(isset($item->modifiers)) {
                if(count($item->modifiers) == 0) {
                    // get the price from item price if not a meal
                    if($category and strtolower($category->translate('en-us')->name) != 'meals') {
                        $newItemPrice += $item->price;
                    }
                }
            }

            // ingredients
            if(isset($item->ingredients) and count($item->ingredients) > 0) {
                foreach($item->ingredients as $ingredient) {
                    $ingredient = (object)$ingredient;

                    // removed this not using the ingredient id anymore
                    //$itemIngredient = ItemIngredient::find($ingredient->id);
                    $item_order->itemOrderIngredients()->save(new ItemOrderIngredient([
                        'item_ingredients_id' => $ingredient->id,
                        'quantity' => $ingredient->quantity
                    ]));
                }
            }*/

            $item_order->price = $newItemPrice;
            $item_order->update();
        }

        /**
         * Foodics order posting
         * todo determine when to process the order for posting
         */
        if($concept_id == 1 or $concept_id == 5) {
            if($payment) {
                $integration = Integration::where('concept_id',$concept_id)->where('type', 'pos')->first();

                if ($integration) {
                    $integrationService = new IntegrationService($request->attributes->get('concept'), $integration);
                    $fs_order = $integrationService->order($order);

                    // todo: verify if this is enough to handle the
                    if(is_null($fs_order)){
                        return response()->json(['error' => [
                            $this->responseArray(1027,400)
                        ]], 400);
                    }

                    if(property_exists($fs_order,'error')){

                        // send an email for failed errors
                        $mailer = new SupportMailer();
                        $mailer->sendFailedPosPostingSD($order,$fs_order,$request);

                        // delete order
                        $order->delete();

                        // get the underlying error in the response
                        $error = (string)$fs_order->error;
                        $error = str_replace('.',' ',$error);

                        // compare from the translations
                        $code = ApiResponse::whereIn('message', function ($query) use ($error) {
                            $query->select('group_id')->from('translations')
                                ->where('value',$error)
                                ->where('locale','en-us');
                        })->value('code');

                        // if cannot get any code return default 1027
                        if(!$code) {
                            $code = 1027;
                        }

                        // return error
                        return response()->json(['error' => [
                            $this->responseArray($code,400)
                        ]], 400);

                    } else {
                        // added the logging of the order POS
                        $order->order_pos_response = json_encode($fs_order);
                        $order->code = $fs_order->order_hid;
                    }
                }
                // set order as posted
                $order->is_posted = 1;
                $order->update();

                // send ably notifications
                //$ably = New NotificationService();
                //$ably->triggerNewOrder($order);
                //dispatch(new NewOrderJob($order));

            }
        } else {
            if($payment) {
                $order->is_posted = 1;
                $order->update();

            }
        }


        // dispatch the order
        dispatch(new NewOrderJob($order));

        /**
         * TODO REMOVE HARD CODED ORDER ASSIGNMENT
         */
        if($concept_id == 1) {
            //if ($location == 13 or $cust == 141) {
            //    $order->employees()->attach(4,['function' => 'driver', 'created_at' => Carbon::now()->setTimezone('GMT+3')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT+3')->toDateTimeString()]);
            //}
        }

        if ($concept_id == 2) {
            $order->employees()->attach(267,['function' => 'driver', 'created_at' => Carbon::now()->setTimezone('GMT+3')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT+3')->toDateTimeString()]);
        }

        if (!$payment) {
            // Has not yet been paid
            $statusId = $concept->default_order_status_card;
        }
        else {
            //$status = $order->is_posted == 1 ? $concept->default_order_status_cash : OrderStatus::where('sequence', 0)->first();
            $statusId = $concept->default_order_status_cash;
        }

        $orderOrderStatus = new OrderOrderStatus();
        $orderOrderStatus->order_status_id = $statusId;
        $orderOrderStatus->order_id = $order->id;
        $orderOrderStatus->save();

        return $this->response->item($order, new OrderTransformer, ['key' => 'order']);
    }

    public function edit(Request $request, $order) {

        $concept = $this->getConcept($request);
        $order = Order::where('id',$order)
            ->first();

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $now = Carbon::now();
        $expire_time = Carbon::parse($order->created_at)->addMinutes($concept->order_cancellation_time);
        //$current_status = $order->currentStatus->first();
        //$cancellation_status = OrderStatus::

        if($now->gte($expire_time) and $order->payment_type != 'card') {
            return response()->json(['error' => [
                $this->responseArray(1048,400)
            ]], 400);
        }

        $integration = Integration::where('concept_id',$concept->id)->where('type', 'pos')->first();
        if($integration){
            $integrationService = new IntegrationService($request->attributes->get('concept'), $integration);
            $status = $integrationService->cancelOrder($order);
            if($order->payment_type == 'card' and strtolower($integration->provider) == 'foodics') {
                $this->payfortMaintenanceFunctions($order,null,'refund');
            }

            if(!$status){
                return response()->json(['error' => [
                    $this->responseArray(1048,400)
                ]], 400);
            }
        }

        $order->statuses()->save(new OrderOrderStatus([
            'order_status_id' => 33,
            'created_at' => Carbon::now()->toDateTimeString()
        ]));

        dispatch(new StatusOrderJob($order));

        return $this->response->item($order, new OrderTransformer, ['key' => 'order']);
    }

    public function getStatusHistory(Request $request,$order)
    {
        $concept = $this->getConcept($request);

        $order = Order::where('id',$order)
            ->where('concept_id', $concept->id)
            ->first();

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $statuses = $order->statuses()->paginate($this->perPage);
        return $this->response->paginator($statuses, new OrderStatusTransformer, ['key' => 'status']);
    }

    private function locationsLookup($concept,$lat,$long)
    {
        $point = new Point($lat,$long);
        $foundStore = null;

        Location::where('concept_id',$concept)
            ->chunk(100,function($locations)use($point,&$foundStore){
                foreach($locations as $location) {
                    $areas =  $location->areas()->get();
                    $found = false;
                    if($areas){
                        foreach($areas as $area){
                            $coordinates = $area->coordinates;
                            $polygonCoordinates = (json_decode('['. $coordinates .']'));
                            $points = [];
                            foreach($polygonCoordinates as $coor){
                                $x = $coor[0];
                                $y = $coor[1];
                                $points[]= new Point($x,$y);
                            }

                            $pls = new PointLocationService($points);
                            if($pls->pointInPolygon($point) !== PointLocationService::OUTSIDE){
                                $foundStore = [
                                    'store' => $location,
                                    'storeArea' => $area
                                ];
                                $found = true;
                                break;
                            }
                        }
                        if($found){
                            break;
                        }
                    }
                }
            });


        if($foundStore){
            $location = $foundStore['store'];
            return $location;
        }

        return false;
    }

    public function getMealPrice(Request $request) {
        $orderItem = json_encode($request->all());
        $service = new HamburginiMealService();
        $price = $service->calculateMealPrice($orderItem);

        return response(['price' => $price], 200)->header('Content-Type', 'application/json');
    }

    public function foodicsHook(Request $request)
    {
        // get all data passed by foodics
        $d = json_decode(json_encode($request->all()),true);

        // log in the logfile
        app('log')->info(print_r($d,true));
        $code = $d['hid'];
        // get order
        $order = Order::where('code',$code)
            ->first();

        if($order) {
            switch($d['status']){
                case 1:
                    $status_id = 25;
                    break;
                case 2:
                    // send payment if authorization and if the order is accepted by foodics
                    $this->payfortMaintenanceFunctions($order,'AUTHORIZATION',null);
                    $status_id = 26;
                    // trigger new order notification
                    dispatch(new NewOrderJob($order));
                    break;
                case 3:
                    // cancel the payment from the order if card.
                    if($order->payment_type == 'card') {
                        $this->payfortMaintenanceFunctions($order,null,'refund');
                    }
                    $status_id = 34;
                    break;
                case 4:
                    $status_id = 32;
                    if($order) {
                        $order->finished_at = Carbon::now()->toDateTimeString();
                        $order->update();
                    }
                    break;
                default:
                    $status_id = 25;
                    break;
            }

            //$status = $order->currentStatus()->first();
            //if ($status == null) {
                $status = new OrderOrderStatus();
                $status->order_id = $order->id;
                $status->order_status_id = $status_id;
                $status->save();
            //}

            //$status->update();

            // trigger new status when order status changes in foodics
            if($d['status'] == 3 or $d['status'] == 4) {
                dispatch(new StatusOrderJob($order));
            }

            if (!isset($order->reference) || $order->reference == '') {
                // Get order reference from foodics
                $concept = $order->concept;
                $integration = Integration::where('concept_id',$concept->id)->where('type', 'pos')->first();
                $integrationService = new FoodicsIntegrationService($concept, $integration);
                $order->reference = $integrationService->getOrderReference($order->code);
                $order->update();
            }


            if ($d['status'] == 4) {
                // Update driver
                app('log')->debug('Updating Driver');
                $concept = $order->concept;
                $integration = Integration::where('concept_id',$concept->id)->where('type', 'pos')->first();
                $integrationService = new FoodicsIntegrationService($concept, $integration);
                $foodicsOrder = $integrationService->getFoodicsOrder($order->code);
                app('log')->debug('Driver present in foodics response:'.isset($foodicsOrder->driver));
                $driverCode = null;
                if (isset($foodicsOrder->driver)) {
                    $driverCode = $foodicsOrder->driver->hid;
                    app('log')->debug('Driver Code:'.$driverCode);

                    // sync driver if not found
                    $integrationService->syncEmployees($driverCode);
                }
                if($driverCode) {
                    $driver = $concept->employees()->where('code', $driverCode)->first();
                    if($driver) {
                        app('log')->debug('Found '.count($driver).' driver');
                        app('log')->debug('DRIVER: '.$driver->username);
                        if ($driver) {
                            $order->employees()->attach($driver->id, ['function' => 'driver','created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString()]);
                        }
                    }
                }
            }

        } /*else {

            switch($d['status']){
                case 1:
                    $status_id = 3; //25
                    break;
                case 2:
                    $status_id = 4; //26
                    break;
                case 3:
                    $status_id = 12; //34
                    break;
                case 4:
                    $status_id = 10; //32
                    break;
                default:
                    $status_id = 3;
                    break;
            }

            // if existing
            $external_order = ExternalOrder::where('order_hid',$code)->first();

            if(!$external_order) {
            // TODO: PUT THIS IN A BACKGROUND JOB
            // get the order details from foodics
            $concepts = Concept::where('default_pos','foodics')->get();
            foreach ($concepts as $concept) {
                $integration = Integration::where('concept_id',$concept->id)->where('type', 'pos')->first();
                if($integration) {
                    $integrationService = new FoodicsIntegrationService($concept, $integration);
                    $foodics_order = $integrationService->getFoodicsOrder($code);
                    if($foodics_order){
                        $external_order_concept = $concept;
                        break;
                    }
                }
            }

            // exit if can't find in foodics
            if(!$foodics_order){
                return $this->response->noContent();
            }

                $external_order = new ExternalOrder();
                $external_order->concept_id = $external_order_concept->id;
                // get location
                $external_order_branch = Location::where('code', $foodics_order->branch->hid)->first();
                $external_order->location_id = $external_order_branch ? $external_order_branch->id : null;
                $external_order->order_hid = $foodics_order->hid;
                $external_order->reference = $foodics_order->reference;
                $external_order->order_time = $foodics_order->opened_at;
                $external_order->promised_time = $foodics_order->due_time;
                $external_order->total = $foodics_order->final_price;
                $external_order->customer_hid = $foodics_order->customer ? $foodics_order->customer->hid : null;
                $external_order->customer = $foodics_order->customer ? $foodics_order->customer->name : null;
                $external_order->customer_phone = $foodics_order->customer ? $foodics_order->customer->phone : null;
                $external_order->delivery_hid = $foodics_order->delivery_address ? json_encode($foodics_order->delivery_address) : null;
                $external_order->delivery_address = $foodics_order->delivery_address ? json_encode($foodics_order->delivery_address) : null;
                //$external_order->delivery_address_longitude = $foodics_order->delivery_address ? $foodics_order->delivery_address->longitude : null;
                //$external_order->delivery_address_latitude = $foodics_order->delivery_address ? $foodics_order->delivery_address->latitude : null;
                // todo double check with the foodics api
                $external_order->payment_hid = $foodics_order->payments ? $foodics_order->payments[0]->hid : null;
                $external_order->payment_amount = $foodics_order->payments ? $foodics_order->payments[0]->amount : null;
                $external_order->payment_method = $foodics_order->payments ? $foodics_order->payments[0]->payment_method->hid : null;
                $external_order->payment_date = $foodics_order->payments ? $foodics_order->payments[0]->actual_date : null;
                $external_order->save();
            }

            $external_order_status = new ExternalOrderOrderStatus();
            $external_order_status->external_order_id = $external_order->id;
            $external_order_status->external_order_status_id = $status_id;
            $external_order_status->save();

        }*/

        return $this->response->noContent();
    }

    public function foodicsPayfortHook(Request $request)
    {
        app('log')->debug('RECEIVED CALL BACK FROM PAYFORT');
        $d = json_decode(json_encode($request->all()),true);
        app('log')->info(print_r($d,true));
        $mailer = new SupportMailer();

        $notification = new NotificationService();

        switch($d) {

            /**
             * command : REFUND
             * response code : 06000
             */
            case $d['response_code'] == 06000 and $d['command'] == 'REFUND':

                app('log')->info('PAYFORT REFUND HOOK:'.json_encode($d,true));

                // return ok
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Transaction received'
                ]], 200);

                break;

            /**
             * command : CAPTURE
             * response code : 04000
             */
            case $d['response_code'] == '04000' and $d['command'] == 'CAPTURE':
                // get the order
                $order = Order::where('id',$d['order_description'])
                            ->first();

                if(!$order){
                    app('log')->error(print_r('order does not exist',true));
                    return response()->json(['ok' => [
                        'status' => true,
                        'message' => 'Transaction received'
                    ]], 200);
                }

                // store the payfort hook data in the db
                app('db')->table('payfort_hooks')->insertGetId([
                    'order_id' => $order->id,
                    'payfort_data' => json_encode($d),
                    'command' => $d['command'],
                    'fort_id' => $d['fort_id'],
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()
                ]);

                app('log')->info('PAYFORT CAPTURE HOOK:'.json_encode($d,true));

                // return ok
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Transaction received'
                ]], 200);

                break;

            /**
             * command : AUTHORIZATION
             * response code : 02000
             */
            case $d['response_code'] == '02000' and $d['command'] == 'AUTHORIZATION':
                // get the order
                $order = $this->_getOrderFromHook($d);

                if(!$order){
                    app('log')->error(print_r('order does not exist',true));
                    return response()->json(['ok' => [
                        'status' => true,
                        'message' => 'Transaction received'
                    ]], 200);
                }

                // store the payfort hook data in the db
                app('db')->table('payfort_hooks')->insertGetId([
                    'order_id' => $order->id,
                    'payfort_data' => json_encode($d),
                    'command' => $d['command'],
                    'fort_id' => $d['fort_id'],
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()
                ]);

                // process order and send to foodics
                $fs_order = $this->_integrationPosting($order,$d);

                // error but log details and send mails in _integrationPosting
                if(!$fs_order) {
                    // reverse the authorization
                    $this->payfortMaintenanceFunctions($order, $d['command'],'void');

                    return response()->json(['ok' => [
                        'status' => true,
                        'message' => 'Transaction received'
                    ]], 200);
                }

                // set order as posted
                $this->_orderUpdate($order,$fs_order,$d);

                // dispatch the new order
                dispatch(new NewOrderJob($order));

                // FOODICS POSTING ORDER SUCCESS
                // SEND CAPTURE COMMAND TO PAYFORT
                /*$payfortConfig = config('payfort')['payfortConfig'][$order->concept->id][$order->concept->default_payfort_config];
                $url = $payfortConfig['url'];
                $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'amount='.$d['amount'].
                        'command=CAPTUREcurrency='.$d['currency'].'fort_id='.$d['fort_id'].'language='.$d['language'].'merchant_identifier='.
                        $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];

                // log payfort signature
                app('log')->info('PAYFORT SIGNATURE PARAMS:'.json_encode($sig_str,true));

                // add the SHA-256 Algorithm
                $sig = hash('sha256',$sig_str);

                $arr_data = [
                    'command' => 'CAPTURE',
                    'access_code' => $payfortConfig['accessCode'],
                    'merchant_identifier' => $payfortConfig['merchantIdentifier'],
                    'merchant_reference' => $d['merchant_reference'],
                    'amount' => $d['amount'],
                    'currency' => $d['currency'],
                    'language' => $d['language'],
                    'fort_id' => $d['fort_id'],
                    'signature' => $sig,
                    'order_description' => $order->id
                ];

                app('log')->info('DATA BEING PASSED FOR CAPTURE: '.json_encode($arr_data,true));
                $data = json_encode($arr_data);

                // send CAPTURE COMMAND to Payfort
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data))
                );

                $result = curl_exec($ch);
                $result = json_decode($result,true);
                app('log')->info('PAYFORT CAPTURE COMMAND RESPONSE API FOR ORDER '.$order->id.' :'.print_r($result,true));

                if($result['response_code'] != '04000') {
                    // added to log the payfort token generation result
                    app('log')->info('PAYFORT CAPTURE ERROR: '.print_r($result,true));

                    // capture failed
                    $mailer->sendFailedCapture($order,$d);

                    return response()->json(['ok' => [
                        'status' => true,
                        'message' => 'Transaction received'
                    ]], 200);

                }

                // send payment email
                $mailer->sendPaymentMail($order,$d);*/

                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Transaction received'
                ]], 200);

                break;

            /**
             * command : PURCHASE
             * response code : 14000
             */
            case $d['response_code'] == '14000' and $d['command'] == 'PURCHASE':

                // get the order
                $order = $this->_getOrderFromHook($d);
                if(!$order){
                    app('log')->info(print_r('order does not exist',true));
                    return response()->json(['ok' => [
                        'status' => true,
                        'message' => 'Transaction received'
                    ]], 200);
                }

                // store the payfort hook data in the db
                app('db')->table('payfort_hooks')->insertGetId([
                    'order_id' => $order->id,
                    'payfort_data' => json_encode($d),
                    'command' => $d['command'],
                    'fort_id' => $d['fort_id'],
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()
                ]);

                // process order and send to foodics
                $fs_order = $this->_integrationPosting($order,$d);

                // error but log details and send mails in _integrationPosting
                if(!$fs_order) {
                    // update order status
                    if($order) {
                        $status = new OrderOrderStatus();
                        $status->order_id = $order->id;
                        $status->order_status_id = 35;
                        $status->save();
                    }

                    // refund order
                    $this->payfortMaintenanceFunctions($order,$d['command'],'refund');

                    /*$payfortConfig = config('payfort')['payfortConfig'][$order->concept->id][$order->concept->default_payfort_config];
                    $url = $payfortConfig['url'];
                    $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'amount='.$d['amount'].
                        'command=REFUNDcurrency='.$d['currency'].'fort_id='.$d['fort_id'].'language='.$d['language'].'merchant_identifier='.
                        $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];

                    // log payfort signature
                    app('log')->info('PAYFORT SIGNATURE PARAMS:'.json_encode($sig_str,true));

                    // add the SHA-256 Algorithm
                    $sig = hash('sha256',$sig_str);

                    $arr_data = [
                        'command' => 'REFUND',
                        'access_code' => $payfortConfig['accessCode'],
                        'merchant_identifier' => $payfortConfig['merchantIdentifier'],
                        'merchant_reference' => $d['merchant_reference'],
                        'amount' => $d['amount'],
                        'currency' => $d['currency'],
                        'language' => $d['language'],
                        'fort_id' => $d['fort_id'],
                        'signature' => $sig,
                        'order_description' => $order->id
                    ];

                    $data = json_encode($arr_data);

                    // send CAPTURE COMMAND to Payfort
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data))
                    );

                    $result = curl_exec($ch);
                    $result = json_decode($result,true);
                    app('log')->info('PAYFORT REFUND COMMAND RESPONSE API FOR ORDER '.$order->id.' :'.print_r($result,true));*/

                    return response()->json(['ok' => [
                        'status' => true,
                        'message' => 'Transaction received'
                    ]], 200);
                }

                // set order as posted
                $this->_orderUpdate($order,$fs_order,$d);

                // send payment email
                $mailer->sendPaymentMail($order,$d);

                // dispatch order
                dispatch(new NewOrderJob($order));

                return response()->json(['ok' => [
                        'status' => true,
                        'message' => 'Transaction received'
                ]], 200);

                break;

            default:
                app('log')->info('ERROR IN PAYFORT WEBHOOK:'.json_encode($d,true));
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Transaction received'
                ]], 200);

        }

        /*if($d['response_code']=='14000'){

            $concept = $order->concept;

            $integration = Integration::where('concept_id',$concept->id)->where('type', 'pos')->first();
            $integrationService = new IntegrationService($concept, $integration);
            $fs_order = $integrationService->order($order);

            if(property_exists($fs_order,'error')){
                // updating of the order pos response
                $order->order_pos_response = json_encode($fs_order);
                $order->update();

                $status = new OrderOrderStatus();
                $status->order_id = $order->id;
                $status->order_status_id = 35;
                $status->save();

                // email notification
                $data = [
                    'order_id' => $order->id,
                    'customer_name' => ucwords($order->customer->first_name).' '.ucwords($order->customer->first_name),
                    'customer_mobile' => $order->customer->mobile,
                    'email' => $d['customer_email'],
                    'fort_id' => $d['fort_id'],
                    'amount' => ((int)$d['amount'] / 100).' '.'SAR',
                    'merchant_reference' => $d['merchant_reference'],
                    'pos' => $concept->default_pos
                ];

                app('mailer')->send('failedorder', $data, function ($message) use ($concept) {
                    $message->subject('Order Posting Failed');
                    $message->to($concept->feedback_email);
                    $message->bcc(['hwhitmore@skylinedynamics.com']);
                    $message->from('support@skylinedynamics.com','Skyline Dynamics Support');
                });

                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Transaction received'
                ]], 200);
            } else {
                // set order as posted
                $order->code = $fs_order->order_hid;
                $order->is_posted = 1;
                $order->order_pos_response = json_encode($fs_order);
                $order->update();

                $status = new OrderOrderStatus();
                $status->order_id = $order->id;
                $status->order_status_id = 25;
                $status->save();

                // add payment object for the order
                $order->payments()->save(new Payment([
                    'method' => 'card',
                    'amount' => ((int)$d['amount'] / 100),
                    'payment_reference_number' => $d['merchant_reference'],
                    'status' => 'success'
                ]));

                // send ably notifications
                //$ably = New NotificationService();
                //$ably->triggerNewOrder($order);
                dispatch(new NewOrderJob($order));


                $data = [
                    'email' => $d['customer_email'],
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'fort_id' => $d['fort_id'],
                    'amount' => ((int)$d['amount'] / 100).' '.'SAR',
                    'merchant_reference' => $d['merchant_reference']
                ];

                app('mailer')->send('payfort', $data, function ($message) use ($concept) {
                    $message->subject('Customer Payment');
                    //$message->to('hwhitmore@skylinedynamics.com');
                    $message->to($concept->feedback_email);
                    $message->bcc(['hwhitmore@skylinedynamics.com','support@skylinedynamics.com']);
                    //$message->bcc('rlamostre@skylinedynamics.com');
                    $message->from('support@skylinedynamics.com','Skyline Dynamics Support');
                });
            }
         } else {
            // add failed payment object for the order
            $order->payments()->save(new Payment([
                'method' => 'card',
                'amount' => ((int)$d['amount'] / 100),
                'payment_reference_number' => $d['merchant_reference'],
                'status' => 'failed'
            ]));
        }*/

        /*return response()->json(['ok' => [
            'status' => true,
            'message' => 'Transaction received'
        ]], 200);*/
    }

    public function foodicsPayfortToken(Request $request)
    {
        // modified this to accommodate the concept
        $concept_id = $request->header('Solo-Concept');
        $concept = Concept::find($concept_id);
        $env = $request->input('env',null);
        $payfortEnv = $env == 'dev' ? 'sandbox' : $concept->default_payfort_config;

        //$payfortConfig = config('payfort')['payfortConfig'][$concept->id][$concept->default_payfort_config];
        $payfortConfig = config('payfort')['payfortConfig'][$concept->id][$payfortEnv];
        $deviceId= $request->json('device-id');
        $language= $request->json('lang');
        $accessCode= $payfortConfig['accessCode'];
        $merchantIdentifier= $payfortConfig['merchantIdentifier'];
        $serviceCommand= 'SDK_TOKEN';
        $requestPhrase= $payfortConfig['requestPhrase'];
        $url= $payfortConfig['url'];

        $headers = $request->headers->all();
        app('log')->info(print_r($headers,true));

        $request=[
            "service_command"=>$serviceCommand,
            "access_code"=>$accessCode,
            "merchant_identifier"=>$merchantIdentifier,
            "language"=>$language,
            "device_id"=>$deviceId,
            "signature"=>hash('sha256', $requestPhrase.implode("",[
                    'access_code='.$accessCode,
                    'device_id='.$deviceId,
                    'language='.$language,
                    'merchant_identifier='.$merchantIdentifier,
                    'service_command='.$serviceCommand,
                ]).$requestPhrase)
        ];

        $data_string = json_encode($request);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);

        // added to log the payfort token generation result
        app('log')->info(print_r($result,true));

        $result=json_decode($result,true);

        if(!isset($result["sdk_token"])){
            return response()->json(['error' => [
                $this->responseArray(1028,400)
            ]], 400);
        }

        return response()->json([
            'SDK_TOKEN' => $result["sdk_token"]
        ], 200);
    }

    public function updateOrderStatus(Request $request, $order)
    {
        $concept = $this->getConcept($request);

        $order = Order::where('id',$order)
            ->where('concept_id', $concept->id)
            ->first();

        $status = trim($request->json('order-status'));

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $order_status = OrderStatus::where('code',$status)
            ->first();

        if(!$order_status){
            return response()->json(['error' => [
                $this->responseArray(1042,404)
            ]], 404);
        }

        $order->statuses()->save(new OrderOrderStatus([
            'order_status_id' => $order_status->id,
            'created_at' => Carbon::now()->toDateTimeString()
        ]));

        if($order_status->id == 32){
            $order->finished_at = Carbon::now()->toDateTimeString();
            $order->update();
        }

        //$ably = New NotificationService();
        //$ably->triggerStatusOrder($order);
        dispatch(new StatusOrderJob($order));

        // this is removed to reduce the time to update the order status
        /*if($status == 'delivery-in-progress'){
            $driver = $order->driver()->first();
            if($driver) {
                $ably->triggerDriverOrder($order,$driver);
            }
        }*/

        if ($order_status->id == 33 || $order_status->id == 34) {
            // Cancel order
            $concept = $this->getConcept($request);
            $integration = Integration::where('concept_id',$concept->id)->where('type', 'pos')->first();
            //TODO proper integration service implementation (don't hardcode Foodics)
            app('log')->debug('calling Foodics cancel :'.json_encode($integration));
            if($integration){
                $integrationService = new FoodicsIntegrationService($concept, $integration);
                $integrationService->cancelOrder($order->code, '');
            }
        }

        return $this->response->item($order, new OrderTransformer(), ['key' => 'order']);
    }

    public function getOrderStatuses(Request $request, $order)
    {
        $order = Order::find($order);

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $statuses = $order->statuses()
            ->orderBy('created_at', 'DESC')
            ->paginate($this->perPage);

        return $this->response->paginator($statuses, new OrderStatusTransformer(), ['key' => 'order-status']);
    }

    public function getOrderItems(Request $request, $order)
    {
        $order = Order::find($order);

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $item_order = $order->orderItems();

        return $this->response->paginator($item_order->paginate($this->perPage), new OrderItemTransformerB(), ['key' => 'order-item']);
    }

    public function getOrderItemsItem(Request $request, $order, $orderItem)
    {
        $item_order = ItemOrder::find($orderItem);

        if(!$item_order){
            return response()->json(['error' => [
                $this->responseArray(1044,404)
            ]], 404);
        }

        return $this->response->item($item_order, new OrderItemTransformerB(), ['key' => 'order-item']);
    }

    public function getAllStatuses(Request $request)
    {
        $filter = $request->input('filter',[]);

        if(array_key_exists('type', $filter)) {
            if($filter['type'] == 'delivery' || $filter['type'] == 'deliver') {
                $statuses = OrderStatus::orderBy('sequence')
                    ->select('id','sequence','code','type','delivery_description')
                    ->paginate($this->perPage);

                return $this->response->paginator($statuses, new OrderStatusesTransformerA(), ['key' => 'order-status']);
            } else {
                $statuses = OrderStatus::orderBy('sequence')
                    ->whereNotIn('id',[29,30,31])
                    ->select('id','sequence','code','type','pickup_description')
                    ->paginate($this->perPage);

                return $this->response->paginator($statuses, new OrderStatusesTransformerB(), ['key' => 'order-status']);
            }
        } else {
            $statuses = OrderStatus::orderBy('sequence')
                ->paginate($this->perPage);
        }

        return $this->response->paginator($statuses, new OrderStatusesTransformer(), ['key' => 'order-status']);

    }

    public function imageUpload(Request $request)
    {
        $image_uri = null;

        if ($request->hasFile('image')) {
            $image_uri = $this->saveUploadedFile($request, 'image');
        }

        return response()->json([
            'data' => [
                'type' => 'image',
                'attributes' => [
                    'status' => 'success',
                    'url' => $image_uri
                ]
            ]
        ]);
    }

    public function giveMoreDetailsAboutFailedOrder(Request $request) {
        $json = json_encode($request->all());
        app('log')->debug('INCOMING ORDER CHECK:'.$json);

        $d = json_decode(json_encode($request->all()),true);

        $items = $request->json('items');

        $decodedItems = array();

        // create each item entry for the order
        foreach ($items as $item) {

            // items
            $item = (object)$item;

            $itemObject = Item::where('id', $item->id)->first();

            $decodedItem = new \stdClass();
            $decodedItem->id = $item->id;
            $decodedItem->name = $itemObject->name;
            $decodedItem->code = $itemObject->code;
            $decodedItem->quantity = $item->quantity;
            $decodedItem->price = $item->price;
            $decodedItem->modifiers = array();
            $decodedItem->ingredients = array();

            if(isset($item->modifiers) and count($item->modifiers) > 0) {
                foreach($item->modifiers as $modifier) {
                    $modifier = (object)$modifier;

                    $modifierObject = Modifier::where('id', $modifier->id)->first();
                    $decodedModifier = new \stdClass();
                    $decodedModifier->id = $modifier->id;
                    $decodedModifier->name = $modifierObject->name;
                    $decodedModifier->code = $modifierObject->code;
                    $decodedModifier->quantity = $modifier->quantity;
                    $decodedModifier->price = $modifier->price;

                    $mg = $modifierObject->modifierGroup;
                    $decodedModifier->modifierGroup = $mg->name;
                    $decodedModifier->modifierGroupCode = $mg->code;
                    $decodedModifier->modifierGroupId = $mg->id;

                    array_push($decodedItem->modifiers, $decodedModifier);
                }
            }

            // ingredients
            if(isset($item->ingredients) and count($item->ingredients) > 0) {
                foreach($item->ingredients as $ingredient) {
                    $ingredient = (object)$ingredient;

                    $iiObject = ItemIngredient::where('id', $ingredient->id)->first();

                    $ingredientObject = $iiObject->ingredient;

                    $decodedIngredient = new \stdClass();
                    $decodedIngredient->id = $ingredientObject->id;
                    $decodedIngredient->name = $ingredientObject->name;
                    $decodedIngredient->code = $ingredientObject->code;

                    array_push($decodedItem->ingredients, $decodedIngredient);

                }
            }

            array_push($decodedItems, $decodedItem);
        }

        $d = json_decode(json_encode($decodedItems),true);

       return response()->json($d);
    }

    private function _getOrderFromHook($d)
    {
        $email = null;
        if(array_key_exists('customer_email',$d))
        {
            $email = $d['customer_email'];
        }

        if(!$email) {
            app('log')->error(print_r('email does not exist', true));
            return $email;
        }

        $customers = Customer::where('email',trim($email))->get();
        if(!$customers){
            app('log')->error(print_r('customer does not exist',true));
            return null;
        }

        // todo: this should be fixed properly
        $order = null;
        foreach($customers as $customer) {
            $order = Order::where('customer_id',$customer->id)
                ->where('is_posted',0)
                ->where('total', $d['amount']/100)
                ->orderBy('created_at','DESC')
                ->take(1)
                ->first();
            if($order) {
                return $order;
            }
        }

        return $order;
    }

    private function _integrationPosting($order,$d)
    {
        $concept = $order->concept;
        $mailer = new SupportMailer();

        $integration = Integration::where('concept_id',$concept->id)->where('type', 'pos')->first();
        $integrationService = new IntegrationService($concept, $integration);
        $fs_order = $integrationService->order($order);

        if(property_exists($fs_order,'error')){
            $order->order_pos_response = json_encode($fs_order);
            $order->update();

            $status = new OrderOrderStatus();
            $status->order_id = $order->id;
            $status->order_status_id = 35;
            $status->save();

            // send failed POS posting email support
            $mailer->sendFailedPosPosting($order,$d);
            // send failed POS posting customer
            $mailer->sendFailedPosPostingCustomer($order,$d);

            return false;
        }

        return $fs_order;
    }

    private function _orderUpdate($order, $fs_order, $d)
    {
        $order->code = $fs_order->order_hid;
        $order->is_posted = 1;
        $order->order_pos_response = json_encode($fs_order);
        $order->update();

        $status = new OrderOrderStatus();
        $status->order_id = $order->id;
        $status->order_status_id = 25;
        $status->save();

        // add payment object for the order
        $order->payments()->save(new Payment([
            'method' => 'card',
            'amount' => ((int)$d['amount'] / 100),
            'payment_reference_number' => $d['merchant_reference'],
            'status' => 'success'
        ]));

        return null;
    }

    private function payfortMaintenanceFunctions($order, $command = null, $type = null)
    {
        $payfort = app('db')->table('payfort_hooks')
            ->where('order_id',$order->id);

        if($command) {
            $payfort = $payfort->where('command',$command);
        }

        $pf_entry = $payfort->orderBy('created_at','DESC')->first();
        app('log')->debug('Hook Type: '.$type.'HOOK DATA COLLECTED: '.json_encode($pf_entry));

        if($pf_entry) {
            // get the data from hook
            $pf_data = (array)json_decode($pf_entry->payfort_data);

            // decide which process should be done

                // void the authorization
                // from payfort hook
                if (
                    strtolower($pf_data['command']) == 'authorization' && $type == 'void' or
                    strtolower($pf_data['command']) == 'authorization' && $type == 'refund'
                ) {
                    app('log')->debug('AUTHORIZATION will be VOIDED in ORDER: '.$order->id);
                    $pf_config = $this->generateSig($order,$pf_data,'VOID_AUTHORIZATION');

                    $arr_data = [
                        'command' => 'VOID_AUTHORIZATION',
                        'access_code' => $pf_config['payfort']['accessCode'],
                        'merchant_identifier' => $pf_config['payfort']['merchantIdentifier'],
                        'merchant_reference' => $pf_data['merchant_reference'],
                        'language' => $pf_data['language'],
                        'fort_id' => $pf_data['fort_id'],
                        'signature' => $pf_config['sig'],
                        'order_description' => $order->id
                    ];

                    app('log')->info('DATA BEING PASSED FOR CAPTURE: '.json_encode($arr_data,true));
                    $data = json_encode($arr_data);

                    // send VOID AUTHORIZATION COMMAND to Payfort
                    $ch = curl_init($pf_config['url']);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data))
                    );

                    $result = curl_exec($ch);
                    $result = json_decode($result,true);
                    app('log')->info('PAYFORT VOID AUTHORIZATION COMMAND RESPONSE API FOR ORDER '.$order->id.' :'.print_r($result,true));
                    return null;
                }


                // refund the purchase
                // from payfort hook
                if (
                    (strtolower($pf_data['command']) == 'purchase' && $type == 'refund') or
                    (strtolower($pf_data['command']) == 'capture' && $type == 'refund') or
                    (strtolower($pf_data['command']) == 'capture' && $type == 'void')
                ) {
                    app('log')->debug($pf_data['command'].' will be VOIDED in ORDER: '.$order->id);
                    // get the payfort data
                    $pf_config = $this->generateSig($order,$pf_data,'REFUND');

                    $arr_data = [
                        'command' => 'REFUND',
                        'access_code' => $pf_config['payfort']['accessCode'],
                        'merchant_identifier' => $pf_config['payfort']['merchantIdentifier'],
                        'merchant_reference' => $pf_data['merchant_reference'],
                        'amount' => $pf_data['amount'],
                        'currency' => $pf_data['currency'],
                        'language' => $pf_data['language'],
                        'fort_id' => $pf_data['fort_id'],
                        'signature' => $pf_config['sig'],
                        'order_description' => $order->id
                    ];

                    app('log')->info('DATA BEING PASSED FOR CAPTURE: '.json_encode($arr_data,true));
                    $data = json_encode($arr_data);

                    // send CAPTURE COMMAND to Payfort
                    $ch = curl_init($pf_config['url']);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data))
                    );

                    $result = curl_exec($ch);
                    $result = json_decode($result,true);
                    app('log')->info('PAYFORT REFUND COMMAND RESPONSE API FOR ORDER '.$order->id.' :'.print_r($result,true));
                    return null;
                }

                // capture payment from authorization
                // from the foodics status hook
                if (strtolower($pf_data['command']) == 'authorization' && $type == null) {
                    app('log')->debug($pf_data['command'].' will be CAPTURED in ORDER: '.$order->id);
                    // get the payfort data
                    $pf_config = $this->generateSig($order,$pf_data,'CAPTURE');

                    $arr_data = [
                        'command' => 'CAPTURE',
                        'access_code' => $pf_config['payfort']['accessCode'],
                        'merchant_identifier' => $pf_config['payfort']['merchantIdentifier'],
                        'merchant_reference' => $pf_data['merchant_reference'],
                        'amount' => $pf_data['amount'],
                        'currency' => $pf_data['currency'],
                        'language' => $pf_data['language'],
                        'fort_id' => $pf_data['fort_id'],
                        'signature' => $pf_config['sig'],
                        'order_description' => $order->id
                    ];

                    app('log')->info('DATA BEING PASSED FOR CAPTURE: '.json_encode($arr_data,true));
                    $data = json_encode($arr_data);

                    // send CAPTURE COMMAND to Payfort
                    $ch = curl_init($pf_config['url']);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data))
                    );

                    $result = curl_exec($ch);
                    $result = json_decode($result,true);
                    app('log')->info('PAYFORT CAPTURE COMMAND RESPONSE API FOR ORDER '.$order->id.' :'.print_r($result,true));
                    return null;
                }

            return null;
        }
        return null;
    }

    private function generateSig($order, $d, $command)
    {
        $payfortConfig = config('payfort')['payfortConfig'][$order->concept->id][$order->concept->default_payfort_config];
        $url = $payfortConfig['url'];

        if ($command == 'AUTHORIZATION') {
            app('log')->debug('AUTHORIZATION');
            $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'command='.$command.'fort_id='.$d['fort_id'].'merchant_identifier='.
                $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];
        }

        if($command == 'VOID_AUTHORIZATION') {
            app('log')->debug('VOID_AUTHORIZATION');
            $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'command='.$command.'fort_id='.$d['fort_id'].'language='.$d['language'].'merchant_identifier='.
                $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];
        }

        if($command == 'REFUND') {
            app('log')->debug('REFUND');
            $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'amount='.$d['amount'].
                'command='.$command.'currency='.$d['currency'].'fort_id='.$d['fort_id'].'language='.$d['language'].'merchant_identifier='.
                $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];
        }

        if($command == 'CAPTURE') {
            app('log')->debug('CAPTURE');
            $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'amount='.$d['amount'].
                'command='.$command.'currency='.$d['currency'].'fort_id='.$d['fort_id'].'language='.$d['language'].'merchant_identifier='.
                $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];
        }

        if(is_null($command)) {
            return null;
        }

        /*if(array_key_exists('customer_email',$d) and $d['customer_email'] != '') {
            $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'amount='.$d['amount'].
                'command='.$command.'currency='.$d['currency'].'customer_email='.$d['customer_email'].'fort_id='.$d['fort_id'].'language='.$d['language'].'merchant_identifier='.
                $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];
        } else {
            $sig_str = $payfortConfig['requestPhrase'].'access_code='.$payfortConfig['accessCode'].'amount='.$d['amount'].
                'command='.$command.'currency='.$d['currency'].'fort_id='.$d['fort_id'].'language='.$d['language'].'merchant_identifier='.
                $payfortConfig['merchantIdentifier'].'merchant_reference='.$d['merchant_reference'].'order_description='.$order->id.$payfortConfig['requestPhrase'];
        }*/

        // log payfort signature
        app('log')->info('PAYFORT SIGNATURE PARAMS FOR ORDER ID '.$order->id.' :'.json_encode($sig_str,true));

        // add the SHA-256 Algorithm
        $sig = hash('sha256',$sig_str);

        // return url and sig
        $array = array(
            'url' => $url,
            'sig' => $sig,
            'payfort' => $payfortConfig
        );

        return $array;
    }
}