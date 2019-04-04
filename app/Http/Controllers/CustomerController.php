<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Customer;
use App\Api\V1\Models\DeliveryArea;
use App\Api\V1\Models\Order;
use App\Api\V1\Models\Location;
use App\Api\V1\Models\CustomerAddress;
use App\Api\V1\Services\SmsDelegate;
use App\Api\V1\Services\CmsDelegate;
use App\Api\V1\Transformers\CustomerTransformer;
use App\Api\V1\Transformers\LocationTransformer;
use App\Api\V1\Transformers\OrderTransformer;
use Illuminate\Http\Request;
use App\Api\V1\Controllers;
use App\Api\V1\Transformers\CustomerAddressTransformer;
use App\Api\V1\Services\PointLocationService;
use App\Api\V1\Models\Point;
use Illuminate\Validation\Rule;

class CustomerController extends ApiController
{

    public function createCustomer(Request $request)
    {
        $data = json_encode($request->except('password'));
        app('log')->debug('Registration Request:'.$data);
        $test = $request->input('test',false);
        $concept_id = $this->getConcept($request)->id;
        $concept = $this->getConcept($request);

        $validator = app('validator')->make($request->all(),[
            //'email' => 'required|email',
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            app('log')->debug('Registration Failed: '.json_encode($validator->errors()));
            return response()->json(['error' => [
                $this->responseArray(1002,400)
            ]], 400);
        }

        if($concept_id == 5) {
            // move username formatting here
            if(preg_match('/[\'^£$:!~,;"`.%&*()}{@#~?><>,|=_+¬-]/', $request->json('username')) || !starts_with($request->json('username'), '0') || strlen(trim($request->json('username'))) > 10 || strlen(trim($request->json('username'))) < 10) {
                app('log')->debug('Burgerizzr Registration Failed, Invalid Username Format: '.$request->json('username'));
                return response()->json(['error' => [
                    $this->responseArray(1045,400)
                ]], 400);
            }

            // Check username is unique
            $validator = app('validator')->make($request->all(),[
                'username' => 'unique:api_subscribers,username',
            ]);

            if ($validator->fails()) {
                app('log')->debug('Burgerizzr Registration Failed, Validation Error: '.json_encode($validator->errors()));
                // add the concept for burgerizzr
                return response()->json(['error' => [
                        $this->responseArray(1051,400)
                ]], 400);
            }
        }


        // check if password is at least 8 chars with 1 letter/number
        $validator = app('validator')->make($request->all(),[
            'password' => 'min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ]);

        if ($validator->fails()) {
            app('log')->debug('Registration Failed, Validation Error: '.json_encode($validator->errors()));
            return response()->json(['error' => [
                $this->responseArray(1019,400)
            ]], 400);
        }

        // added validation for duplicate usernames
        if($concept_id != 5){
            $validator = app('validator')->make($request->all(),[
                'username' => Rule::unique('api_subscribers')->where(function($query) use ($concept) {
                    $query->where('client_id', $concept->client_id);
                })
            ]);

            if ($validator->fails()) {
                app('log')->debug($concept->label.' Registration Failed, Validation Error: '.json_encode($validator->errors()));
                if ($concept_id == 3 or $concept_id == 4) {
                    return response()->json(['error' => [
                        $this->responseArray(1053,400)
                    ]], 400);
                }
                return response()->json(['error' => [
                    $this->responseArray(1008,400)
                ]], 400);
            }
        }

        $mobile = $request->json('mobile');
        $mobile = str_replace('+','',$mobile);
        $mobile_format = starts_with($mobile, '0') ? substr($mobile, 1) : $mobile ;
        $mobile_format = !starts_with($mobile_format, $concept->dialing_code) &&  strlen(trim($mobile_format)) < 10 ? $concept->dialing_code.$mobile_format : $mobile_format;
        $mobileLength = strlen(trim($mobile_format));

        if (
            !$mobile ||
            ($mobileLength < 12 || $mobileLength > 13) ||
            preg_match('/[\'^£$:!~,;"`.%&*()}{@#~?><>,|=_+¬-]/', $mobile)
        ) {
            app('log')->debug('Registration Failed: Invalid Mobile number format'.$mobile);
            return response()->json(['error' => [
                $this->responseArray(1003,400)
            ]], 400);
        }

        /*switch($concept_id) {
            case 1:
            case 2:
            case 3:
            case 4:
                $username = trim($request->json('username'));
                $mobile_cc = $mobile_format;
                break;
            case 5:
                $username = trim($request->json('username'));
                $mobile_cc = $mobile;
                break;
            default:
                $username = trim($request->json('username'));
                $mobile_cc = $mobile_format;
                break;
        }*/

        $code = $test ? '123456' : $this->generateCode();

        // todo: what to put in `provider_id` columm?

        $customer = new Customer();
        $customer->first_name = $request->json('first-name');
        $customer->sms_code = $code;
        $customer->last_name = $request->json('last-name');
        $customer->email = $request->json('email');
        $customer->mobile = $mobile_format;
        $customer->status = 'unverified';
        $customer->account_type = $request->json('account-type');
        $customer->save();

        $customer->concepts()->attach($concept_id);

        $this->createApiSubscriber(
          'customer',
          $customer->id,
          trim($request->json('username')),
          $request->json('password'),
          $this->getConcept($request)->client_id
        );

        $rs = false;
        try {
            $sms = new SmsDelegate($this->getConcept($request)->id);
            $cms = new CmsDelegate($this->getConcept($request)->id);
            $body = $cms->getContentByKey('sms_otp').' '.$code;
            app('log')->debug('BODY:'.$body);
            $rs = $sms->send($body,$customer->mobile);
        }
        catch (\Exception $e) {
        }

        if(!$rs) {
            $subscriber = $customer->user()->first();
            $subscriber->delete();
            $customer->delete();

            return response()->json(['error' => [
                $this->responseArray(1004,400)
            ]], 400);
        }

        return $this->response->item($customer, new CustomerTransformer(), ['key' => 'customer'])->setStatusCode(201);
    }

    public function customerSmsVerification(Request $request, $customer)
    {
        $customer = Customer::where('id', $customer)->where('sms_code',trim($request->json('pincode')))
            ->first();

        if(!$customer) {
            return response()->json(['error' => [
                $this->responseArray(1012,404)
            ]], 404);
        }

        $customer->verified();

        return $this->response->item($customer, new CustomerTransformer(), ['key' => 'customer'])->setStatusCode(200);
    }

    public function index(Request $request)
    {
        $concept = $this->getConcept($request);
        $customers = $concept->customers();
        $filter = $request->input('filter',[]);

        if(array_key_exists('customer',$filter)){
            $customer = strtolower($filter['customer']);
            // check if there is a name / id
            if (strpos(trim($customer), ',')) {
                // customer name are separated by comma
                $_name = explode(',', $customer, 2);
                $customers->where(function($query) use ($_name) {
                    $query->orWhere('first_name', 'LIKE', '%' . $_name[0] . '%');
                    $query->orWhere('first_name', 'LIKE', '%' . $_name[1] . '%');
                    $query->orWhere('last_name', 'LIKE', '%' . $_name[0] . '%');
                    $query->orWhere('last_name', 'LIKE', '%' . $_name[1] . '%');
                });

            } elseif(strpos(trim($customer), ' ')) {
                // customer name are separated by space
                $_name = preg_split('/\s+/', $customer, -1, PREG_SPLIT_NO_EMPTY);
                $customers->where(function($query) use ($_name){
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
                    $customers->where('id',$customer);
                } else {
                    $customers->where(function ($query) use ($customer) {
                        $query->orWhere('first_name', 'LIKE', '%' . $customer . '%');
                        $query->orWhere('last_name', 'LIKE', '%' . $customer . '%');
                    });
                }
            }
        }

        if(array_key_exists('mobile',$filter)){
            $mobile = strtolower($filter['mobile']);
            $mobile = ltrim($mobile,'0');
            $mobile = starts_with($mobile, '966') ? str_replace('966','',$mobile) : $mobile ;
            $customers->where('mobile','LIKE','%'.$mobile.'%');
        }

        if(array_key_exists('email',$filter)){
            $email = strtolower($filter['email']);
            $customers->where('email','LIKE','%'.$email.'%');
        }

        $customers->orderBy('created_at','DESC');

        return $this->response->paginator($customers->paginate($this->perPage), new CustomerTransformer(), ['key' => 'customer']);
    }

    public function show(Request $request, $customer)
    {
        $customer = Customer::where('id',$customer)
            ->first();

        if(!$customer) {
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        return $this->response->item($customer, new CustomerTransformer(), ['key' => 'customer']);
    }

    public function edit(Request $request, $customer)
    {
        $customer = Customer::where('id',$customer)
            ->first();

        if(!$customer) {
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        if($request->has('first-name')) {
            $customer->first_name = $request->input('first-name');
        }

        if($request->has('last-name')) {
            $customer->last_name = $request->input('last-name');
        }

        if($request->has('email')) {
            $customer->email = $request->input('email');
        }

        if($request->has('mobile')) {
            $concept = $this->getConcept($request);
            $customer->mobile = $request->input('mobile');

            $mobile = $request->json('mobile');
            $mobile = str_replace('+','',$mobile);
            $mobile_format = starts_with($mobile, '0') ? substr($mobile, 1) : $mobile ;
            $mobile_format = !starts_with($mobile_format, $concept->dialing_code) &&  strlen(trim($mobile_format)) < 10 ? $concept->dialing_code.$mobile_format : $mobile_format;
            $mobileLength = strlen(trim($mobile_format));

            if($customer->status == 'unverified') {
                if (
                    !$mobile ||
                    ($mobileLength < 12 || $mobileLength > 13) ||
                    preg_match('/[\'^£$:!~,;"`.%&*()}{@#~?><>,|=_+¬-]/', $mobile)
                ) {
                    app('log')->debug('Resend OTP Message Failed: Invalid Mobile number format'.$mobile);
                } else {
                    try {
                        app('log')->debug('Customer Status Unverified for Customer:'.$customer->id);
                        $code = $this->generateCode();
                        $sms = new SmsDelegate($concept->id);
                        $cms = new CmsDelegate($concept->id);
                        $body = $cms->getContentByKey('sms_otp').' '.$code;
                        app('log')->debug('BODY:'.$body);
                        $sms->send($body,$request->input('mobile'));
                    }
                    catch (\Exception $e) {
                        app('log')->error('BODY:'.$e->getMessage());
                    }
                }
            }
        }

        $customer->update();

        // add update password if the request is there
        if ($request->has('password')) {

            $api_subscriber = $customer->user()->first();

            $validator = app('validator')->make($request->all(),[
                'password' => 'min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => [
                    $this->responseArray(1019,400)
                ]], 400);
            }

            if($api_subscriber) {
                $api_subscriber->password = trim($request->input('password'));
                $api_subscriber->update();
            }
        }

        return $this->response->item($customer, new CustomerTransformer(), ['key' => 'customer']);
    }

    public function showAddresses(Request $request, $customer)
    {
        $customer = Customer::where('id',$customer)
            ->first();

        if(!$customer) {
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        $addresses = $customer->addresses()
            ->orderBy('created_at','DESC')
            ->enabled()
            ->paginate($this->perPage);

        return $this->response->paginator($addresses, new CustomerAddressTransformer(), ['key' => 'address']);
    }

    public function showAddress(Request $request, $customer, $address)
    {
        $address = CustomerAddress::where('customer_id', $customer)
            ->where('id', $address)
            ->first();

        if(!$address) {
            return response()->json(['error' => [
                $this->responseArray(1006,404)
            ]], 404);
        }

        return $this->response->item($address, new CustomerAddressTransformer(), ['key' => 'address']);
    }

    public function addAddress(Request $request, $customer)
    {
        $customer = $customer = Customer::where('id',$customer)
            ->first();

        if(!$customer) {
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        $concept_id = $this->getConcept($request)->id;

        $lat = trim($request->json('lat',null));
        $long = trim($request->json('long',null));
        $delivery_area_id = $request->json('delivery-area-id',null);

        if($lat and $long) {
            $result = $this->locationsLookup($concept_id,$lat,$long);

            if(!$result){
                app('db')->table('unsupported_locations')
                    ->insert([
                        'customer_id' => $customer->id,
                        'latitude' => $lat,
                        'longitude' => $long
                    ]);
                return response()->json(['error' => [
                    $this->responseArray(1011,400)
                ]], 400);
            }
        }

        if(
            (!$lat or !$long) and !$delivery_area_id
        ) {
            return response()->json(['error' => [
                $this->responseArray(1059,400)
            ]], 400);
        }

        $customer->addresses()->create([
            'customer_id' => $customer->id,
            'label' => trim($request->json('label')),
            'lat' => $request->json('lat'),
            'long' => $request->json('long'),
            'telephone' => $request->json('telephone'),
            'instructions' => $request->json('instructions'),
            'city' => $request->json('city'),
            'country' => $request->json('country'),
            'postal_code' => $request->json('postal-code'),
            'line1' => $request->json('line1'),
            'line2' => $request->json('line2'),
            'state' => $request->json('state'),
            'enabled' => 1,
            'delivery_area_id' => $delivery_area_id
        ]);

        return $this->response->paginator($customer->addresses()->orderBy('created_at','DESC')->paginate($this->perPage), new CustomerAddressTransformer(), ['key' => 'customer']);
    }


    public function editAddress(Request $request, $address)
    {
        $address = CustomerAddress::where('id',$address)
            ->first();

        if(!$address)
        {
            return response()->json(['error' => [
                $this->responseArray(1006,404)
            ]], 404);
        }

        if($request->has('label')) {
            $address->label = $request->input('label');
        }
        if($request->has('lat')) {
            $address->lat = $request->input('lat');
        }
        if($request->has('long')) {
            $address->long = $request->input('long');
        }
        if($request->has('telephone')) {
            $address->telephone = $request->input('telephone');
        }
        if($request->has('instructions')) {
            $address->instructions = $request->input('instructions');
        }
        if($request->has('city')) {
            $address->city = $request->input('city');
        }
        if($request->has('country')) {
            $address->country = $request->input('country');
        }
        if($request->has('postal-code')) {
            $address->postal_code = $request->input('postal-code');
        }
        if($request->has('line1')) {
            $address->line1 = $request->input('line1');
        }
        if($request->has('line2')) {
            $address->line2 = $request->input('line2');
        }
        if($request->has('state')) {
            $address->state = $request->input('state');
        }
        if($request->has('enabled')) {
            $address->enabled = $request->input('enabled');
        }
        if($request->has('delivery-area-id')) {
            $address->delivery_area_id = $request->input('delivery-area-id');
        }
        $address->update();

        return $this->response->item($address, new CustomerAddressTransformer(), ['key' => 'address']);
    }

    public function deleteAddress(Request $request, $customer, $address)
    {
        $customer = Customer::find($customer);

        if(!$customer) {
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        $address = CustomerAddress::find($address);

        if(!$address) {
            return response()->json(['error' => [
                $this->responseArray(1006,404)
            ]], 404);
        }

        $address->enabled = 0;
        $address->save();

        return $this->response->paginator($customer->addresses()->enabled()->paginate($this->perPage), new CustomerAddressTransformer(), ['key' => 'customer']);
    }

    public function customerOrders(Request $request, $customer)
    {
        app('log')->debug('IN CUSTOMER ORDERS');
        app('log')->debug('Customer ID: '.$customer);

        $orders = Order::where('customer_id',$customer)
            ->orderBy('created_at','desc')
            ->paginate($this->perPage);

        return $this->response->paginator($orders, new OrderTransformer(), ['key' => 'orders']);
    }

    public function resendSms(Request $request)
    {
        $concept = $this->getConcept($request);
        $test = $request->input('test',false);

        $validator = app('validator')->make($request->all(),[
            'mobile' => 'required',
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => [
                $this->responseArray(1004,400)
            ]], 400);
        }

        $id = $request->json('id');
        /*$mobile = str_replace('+','',$request->json('mobile'));
        $mobileLength = strlen(trim($mobile));*/

        $mobile = $request->json('mobile');
        $mobile = str_replace('+','',$mobile);
        $mobile_format = starts_with($mobile, '0') ? substr($mobile, 1) : $mobile ;
        $mobile_format = !starts_with($mobile_format, $concept->dialing_code) &&  strlen(trim($mobile_format)) < 10 ? $concept->dialing_code.$mobile_format : $mobile_format;
        $mobileLength = strlen(trim($mobile_format));

        if (
            !$mobile ||
            ($mobileLength < 12 || $mobileLength > 13) ||
            preg_match('/[\'^£$:!~,;"`.%&*()}{@#~?><>,|=_+¬-]/', $mobile)
        ) {
            return response()->json(['error' => [
                $this->responseArray(1003,400)
            ]], 400);
        }

        // todo: remove concept hardcode for munch
        $code = $test ? '123456': $this->generateCode();

        $customer = Customer::where('id',$id)
            ->first();
        $customer->mobile = $mobile_format;
        $customer->sms_code = $code;
        $customer->save();

        try {
            $sms = new SmsDelegate($this->getConcept($request)->id);
            $cms = new CmsDelegate($this->getConcept($request)->id);
            $body = $cms->getContentByKey('sms_otp').' '.$code;
            app('log')->debug('BODY:'.$body);
            $rs = $sms->send($body,$mobile_format);
        }
        catch (\Exception $e) {
        }

        if(!$rs) {
            return response()->json(['error' => [
                $this->responseArray(1004,400)
            ]], 400);
        }

        return $this->response->item($customer, new CustomerTransformer(), ['key' => 'customer']);
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
            return true;
        }

        return false;
    }

    public function custAddressLocLookup(Request $request, $customer, $address)
    {

        $address = CustomerAddress::where('customer_id',$customer)
            ->where('id',$address)
            ->first();

        if(!$address){
            return response()->json(['error' => [
                $this->responseArray(1006,404)
            ]], 404);
        }

        $point = new Point($address->lat,$address->long);
        $foundStore = null;

        Location::where('concept_id',$this->getConcept($request)->id)
            ->chunk(20,function($locations)use($point,&$foundStore){
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
                                    'store'=> $location,
                                    'storeArea'=>$area
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
            return $this->response->item($location, new LocationTransformer, ['key' => 'location']);
        }

        return response()->json(['error' => [
            $this->responseArray(1009,404)
        ]], 404);
    }

    public function getCustomerFavOrder(Request $request, $customer)
    {
        $orders = Order::where('customer_id',$customer)
            ->where('customer_favorite',1)
            ->paginate($this->perPage);

        if(!$orders) {
            return response()->json(['error' => [
                $this->responseArray(1020,404)
            ]], 404);
        }

        return $this->response->paginator($orders, new OrderTransformer(), ['key' => 'order']);
    }

    public function setCustomerFavOrder(Request $request, $customer)
    {
        $orders = Order::where('customer_id',$customer)
            ->where('id',trim($request->json('order')))
            ->first();

        if(!$orders) {
            return response()->json(['error' => [
                $this->responseArray(1020,404)
            ]], 404);
        }

        $orders->customer_favorite = 1;
        $orders->save();

        return $this->response->item($orders, new OrderTransformer(), ['key' => 'order']);
    }

    public function deleteCustomerFavOrder(Request $request, $customer, $order)
    {
        $orders = Order::where('customer_id',$customer)
            ->where('id',$order)
            ->first();

        if(!$orders) {
            return response()->json(['error' => [
                $this->responseArray(1020,404)
            ]], 404);
        }

        $orders->customer_favorite = 0;
        $orders->save();

        return $this->response->item($orders, new OrderTransformer(), ['key' => 'order']);
    }


}