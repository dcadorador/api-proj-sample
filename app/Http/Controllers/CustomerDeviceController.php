<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Models\Customer;
use App\Api\V1\Models\ApiSubscriber;
use App\Api\V1\Models\CustomerDevice;
use App\Api\V1\Transformers\CustomerDeviceTransformer;
use App\Api\V1\Services\SnsService;
use Tymon\JWTAuth\JWTAuth;
use JWTFactory;

class CustomerDeviceController extends ApiController
{
    public function __construct(SnsService $sns, JWTAuth $auth)
    {
        $this->sns = $sns;
        $this->auth = $auth;
    }

    public function index(Request $request, $customer)
    {
        $customer = Customer::where('id',$customer)
            ->first();

        if(!$customer) {
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        $devices = $customer->devices->paginate($this->perPage);
        return $this->response->paginator($devices, new CustomerDeviceTransformer, ['key' => 'customer-device']);
    }

    public function editLanguage(Request $request)
    //public function editLanguage(Request $request, $customer)
    {
        app('log')->debug('INCOMING CHANGE LANGUAGE REQUEST:'.json_encode($request->all()));
        app('log')->debug('INCOMING CHANGE LANGUAGE HEADER:'.json_encode($request->headers->all()));
        $concept = $this->getConcept($request);
        $lang = $request->has('lang') ? $request->input('lang') : $request->header('Accept-Language','ar-sa');
        $customer_id = $request->input('customer-id',null);
        $device_id = $request->input('device-id',null);
        $device_token = $request->input('device-token',null);

        // subscribe to the new topic endpoint language
        $application = $concept->applications()->first();

        // return of either both device id and device token
        if(!$application or !$device_id) {
            return $this->response->noContent();
        }

        $broadcast_arn = $application->broadcast_topic_arn;
        $broadcast_arn = (array)json_decode($broadcast_arn);
        $topic_arn = $lang == 'ar-sa' ? $broadcast_arn['ar-sa'] : $broadcast_arn['en-us'];

        // retrieve the device using CustomerDevice
        if($device_id) {
            $device = CustomerDevice::where('device_id',$device_id)->where('application_id',$application->id);

            // check if device token is present
            if($device_token) {
                $device = $device->where('device_token',$device_token);
            }

            // check if customer is present
            //if($customer_id) {
            //    $device = $device->where('customer_id',$customer_id);
            //}

            $device = $device->first();

            if($device) {
                $device->current_language = $lang;
                $device->update();

                // unsubscribe to the existing endpoint of the aws sns
                if($device->topic_subscription_arn and $device->topic_subscription_arn != '') {
                    try {
                        $this->sns->unsubscribeEndpoint($device->topic_subscription_arn);
                    } catch (\Exception $e) {
                        app('log')->debug('Unsubscribe Error: '.json_encode($e->getMessage()));
                    }
                }

                if($device->endpoint_arn and $device->endpoint_arn != '') {
                    try{
                        $result = $this->sns->setEndpointSubscription($device->endpoint_arn,'application',$topic_arn);
                        if($result){
                            $result = $result->get('SubscriptionArn');
                            $device->topic_subscription_arn = $result;
                            $device->update();
                        }
                        app('log')->debug('Subscription Result: '.json_encode($result));
                    } catch(\Exception $e) {
                        app('log')->debug('Subscription Error: '.json_encode($e->getMessage()));
                    }
                }
            }
        }

        /*if($customer_id) {
            $customer = Customer::find($customer_id);
            if($customer) {
                $devices = $customer->devices()->get();
                if($devices) {
                    foreach($devices as $device){
                        if($device) {
                            $device->current_language = $lang;
                            $device->update();

                            if($device->topic_subscription_arn and $device->topic_subscription_arn != '') {
                                $this->sns->unsubscribeEndpoint($device->topic_subscription_arn);
                            }

                            if($device->endpoint_arn and $device->endpoint_arn != '') {
                                try{
                                    $result = $this->sns->setEndpointSubscription($device->endpoint_arn,'application',$topic_arn);
                                    if($result){
                                        $result = $result->get('SubscriptionArn');
                                        $device->topic_subscription_arn = $result;
                                        $device->update();
                                    }
                                    app('log')->debug('Subscription Result: '.json_encode($result));
                                } catch(\Exception $e) {
                                    app('log')->debug('Subscription Error: '.json_encode($e->getMessage()));
                                }
                            }

                        }
                    }
                }
            }
        } elseif($device_id and $device_token) {
            $device = CustomerDevice::where('device_token',$device_token)
                ->where('device_id',$device_id)
                ->first();
            if($device) {
                $device->current_language = $lang;
                $device->update();

                // unsubscribe to the current topic endpoint language
                if($device->topic_subscription_arn and $device->topic_subscription_arn != '') {
                    $this->sns->unsubscribeEndpoint($device->topic_subscription_arn);
                }

                if($device->endpoint_arn and $device->endpoint_arn != '') {
                    try{
                        $result = $this->sns->setEndpointSubscription($device->endpoint_arn,'application',$topic_arn);
                        if($result){
                                $result = $result->get('SubscriptionArn');
                                $device->topic_subscription_arn = $result;
                                $device->update();
                        }
                        app('log')->debug('Subscription Result: '.json_encode($result));
                    } catch(\Exception $e) {
                        app('log')->debug('Subscription Error: '.json_encode($e->getMessage()));
                    }
                }
            }
        }*/

        return $this->response->noContent();
    }

    public function identify(Request $request)
    {
        app('log')->debug('INCOMING DEVICE IDENTIFY REQUEST:'.json_encode($request->all()));
        app('log')->debug('INCOMING DEVICE IDENTIFY HEADER:'.json_encode($request->headers->all()));

        // validate the request
        $validator = app('validator')->make($request->all(),[
            'device-id' => 'required',
            'device-token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => [
                $this->responseArray(1055,400)
            ]], 400);
        }

        // get concept
        $concept = $this->getConcept($request);

        // get device model
        $user_agent = $request->header('User-Agent');

        if(!is_object(json_decode($user_agent))) {
            $user_agent = str_replace("'",'"',$user_agent);
        }

        $app_version = (array)json_decode($user_agent);
        $os = array_key_exists('OS',$app_version) ? strtolower($app_version['OS']) : null;
        $model = array_key_exists('Device',$app_version) ? strtolower($app_version['Device']) : null;

        $device_id = $request->input('device-id');
        $device_token = $request->input('device-token');
        $customer_id = $request->input('customer-id',null);
        $mobile =  $request->input('mobile',null);

        // get application id from concept
        $application = $concept->applications()->first();
        $broadcast_arn = $application->broadcast_topic_arn;
        $broadcast_arn = (array)json_decode($broadcast_arn);

        $topic_arn = null;

        // return of either both device id and device token
        if(!$application) {
            app('log')->error('Application does not exist');
            return $this->response->noContent();
        }

        if(strpos(strtolower($os), 'ios') !== false){
            $arn = $application->apple_arn;
        } else {
            $arn = $application->google_arn;
        }

        if(!$device_id) {
            return response()->json(['error' => [
                $this->responseArray(1059,400)
            ]], 400);
        }

        // get the customer device by device-id
        $customer_device = CustomerDevice::where('device_id',$device_id);

        if($device_token and $device_token != '') {
            $customer_device->where('device_token',$device_token);
        }

        $customer_device = $customer_device->first();

        // get the customer
        if($customer_id){
            $customer = Customer::find($customer_id);
        }

        // if device is not existing
        if(
            !$customer_device or
            // added this to check if the device is existing but is using a different concept application
            (
                $customer_device and
                $customer_device->application_id != $application->id
            )
        ) {
            // get the current language of the app
            $lang = $request->header('Accept-Language') ? $request->header('Accept-Language') : 'ar-sa';

            // get endpoint arn for the device
            $amazonCustomData = $customer_id ? $customer->first_name." ".$customer->last_name : ucwords($concept->label).'-'.$device_id;
            $endpointArn = $this->return_arn_endpoint($amazonCustomData, $arn, $device_token);

            $customer_device = new CustomerDevice();
            $customer_device->customer_id = $customer_id ? $customer_id: null;
            $customer_device->application_id = $application ? $application->id : null;
            $customer_device->device_token = $device_token;
            $customer_device->device_id = $device_id;
            $customer_device->model = $model;
            $customer_device->endpoint_arn = $endpointArn;
            $customer_device->current_language = $lang;
            $customer_device->save();
        } else {
            // if device is existing
            if($customer_device->endpoint_arn) {
                $endpointAtt = $this->sns->getEndpointAttributes($customer_device->endpoint_arn,$device_token);
                $amazonCustomData = $device_token." ".$device_id;

                if($endpointAtt < 0){
                    $endpointArn = $this->sns->registerDevice($amazonCustomData,$arn, $device_token);
                    $customer_device->endpoint_arn = $endpointArn;

                } else { // Endpoint is either have invalid token or it is marked as disabled.

                    $cust_ea = $customer_device->endpoint_arn;
                    $temp_arn = explode("/",$arn);

                    if(isset($temp_arn[2])){
                        //search this in the customer's endpoint arn;
                        if (strpos($cust_ea, $temp_arn[2]) === false) {
                            $customer_device->endpoint_arn = $this->sns->registerDevice($amazonCustomData,$arn, $device_token);
                        }
                    }
                    try {
                        $this->sns->setEndpointAttributes($device_token,'true',(string)$customer_device->endpoint_arn);
                    } catch (\Exception $e) {
                        app('log')->error('Send Endpoint Attribute Error'.$customer_device->topic_subscription_arn);
                    }

                }
                $customer_device->update();
            }
        }

        // subscribe to the correct arn
        $topic_arn = $customer_device->current_language == 'ar-sa' ? $broadcast_arn['ar-sa'] : $broadcast_arn['en-us'];

        // added for security measures
        if($customer_device->topic_subscription_arn != '' or !is_null($customer_device->topic_subscription_arn)) {
            try {
                app('log')->info('Unsubscribe topic arn: '.$customer_device->topic_subscription_arn);
                $this->sns->unsubscribeEndpoint($customer_device->topic_subscription_arn);
            } catch (\Exception $e) {
                app('log')->debug('Unsubscribe Error: '.json_encode($e->getMessage()));
            }
        }

        $result = $this->sns->setEndpointSubscription($customer_device->endpoint_arn,'application',$topic_arn);

        if($result){
            app('log')->info('Subscription successful: '.$result->get('SubscriptionArn').' - Device ID: '.$customer_device->device_id);
            $result = $result->get('SubscriptionArn');
            $customer_device->topic_subscription_arn = $result;
            $customer_device->update();
        }

        // if customer is existing
        if($customer_id) {
            if(
                !$customer_device->customer_id or
                is_null($customer_device->customer_id) or
                $customer_device->customer_id == ''
            ) {
                app('log')->info('Device existing but customer is null, assigning to customer'.$customer_id);
                $customer_device->customer_id = $customer_id;
                $customer_device->update();
            }

            // if the customer_id is not the same as the customer being sent through the identify request, create the jwt for the customer being identified not the existing one.
            if(
                $customer_device->customer_id and
                $customer_device->customer_id != $customer_id
            ){
                $customer = Customer::find($customer_id);
                app('log')->info('Device existing for user: '.$customer_device->customer_id.'| But new user is using same device: '.$customer_id);
            }

            $api_subscriber = ApiSubscriber::with('userable')
                ->where('client_id',$concept->client_id)
                ->where('userable_id',$customer->id)->first();
            $payload = JWTFactory::setTTL(74649600)->sub($api_subscriber->id)->make();
        } else {
            // create custom claims
            app('log')->info('No customer provided creating custom claims and using device id as JWT SUB parameter for customer device id: '.$customer_device->id);
            $custom_claims = [
                'customer_device_id' => $customer_device->id,
                'customer_device_token' => $customer_device->token,
                'concept' => $concept->label
            ];
            $payload = JWTFactory::setTTL(74649600)->customClaims($custom_claims)->sub($customer_device->id)->make();
        }

        $token = $this->auth->encode($payload)->get();
        $customer_device->token = $token;

        return $this->response->item($customer_device, new CustomerDeviceTransformer, ['key' => 'customer-device']);
        // if the device has passed the customer id
        /*if($customer_id) {

            // check if the both customer and device are existing
            $customer_device = null;
            $customer = Customer::find($customer_id);
            if($customer) {
                $customer_device = $customer->devices()->where('device_id',$device_id)
                    ->first();
            }

            // if existing send jwt token as login
            if(!$customer_device) {
                // get the current language of the app
                $lang = $request->header('Accept-Language') ? $request->header('Accept-Language') : 'ar-sa';

                // get endpoint arn for the device
                $amazonCustomData = $customer ? $customer->first_name." ".$customer->last_name : null;
                $endpointArn = $this->return_arn_endpoint($amazonCustomData, $arn, $device_token);

                $customer_device = new CustomerDevice();
                $customer_device->customer_id = $customer_id;
                $customer_device->application_id = $application ? $application->id : null;
                $customer_device->device_token = $device_token;
                $customer_device->device_id = $device_id;
                $customer_device->model = $model;
                $customer_device->endpoint_arn = $endpointArn;
                $customer_device->current_language = $lang;
                $customer_device->save();
            }

            // subscribe to the correct arn
            $topic_arn = $customer_device->current_language == 'ar-sa' ? $broadcast_arn['ar-sa'] : $broadcast_arn['en-us'];
            $result = $this->sns->setEndpointSubscription($customer_device->endpoint_arn,'application',$topic_arn);

            if($result){
                $result = $result->get('SubscriptionArn');
                $customer_device->topic_subscription_arn = $result;
                $customer_device->update();
            }

            //
            $api_subscriber = ApiSubscriber::with('userable')
                ->where('client_id',$concept->client_id)
                ->where('userable_id',$customer->id)->first();

            if(!$api_subscriber) {
                return response()->json(['error' => [
                    $this->responseArray(1005,404)
                ]], 404);
            }

            $payload = JWTFactory::setTTL(74649600)->sub($api_subscriber->id)->make();
            $token = $this->auth->encode($payload)->get();
            $customer_device->token = $token;

            return $this->response->item($customer_device, new CustomerDeviceTransformer, ['key' => 'customer-device']);

        } else {

            // there is no customer id
            // check if the device is existing
            $customer_device = CustomerDevice::where('device_id',$device_id)
                ->first();

            if(!$customer_device) {
                $lang = $request->header('Accept-Language');
                $amazonCustomData = $device_token.' '.$device_id;
                $endpointArn = $this->return_arn_endpoint($amazonCustomData, $arn, $device_token);
                $customer_device = new CustomerDevice();
                $customer_device->customer_id = $customer_id;
                $customer_device->application_id = $application ? $application->id : null;
                $customer_device->device_token = $device_token;
                $customer_device->device_id = $device_id;
                $customer_device->model = $model;
                $customer_device->endpoint_arn = $endpointArn;
                $customer_device->current_language = $lang;
                $customer_device->save();

            } else {

                // if device is existing
                $endpointAtt = $this->sns->getEndpointAttributes($customer_device->endpoint_arn,$device_token);
                $amazonCustomData = $device_token." ".$device_id;

                if($endpointAtt < 0){
                    $endpointArn = $this->sns->registerDevice($amazonCustomData,$arn, $device_token);
                    $customer_device->endpoint_arn = $endpointArn;

                } else { // Endpoint is either have invalid token or it is marked as disabled.

                    $cust_ea = $customer_device->endpoint_arn;
                    $temp_arn = explode("/",$arn);

                    if(isset($temp_arn[2])){
                        //search this in the customer's endpoint arn;
                        if (strpos($cust_ea, $temp_arn[2]) === false) {
                            $customer_device->endpoint_arn = $this->sns->registerDevice($amazonCustomData,$arn, $device_token);
                        }
                    }
                    $this->sns->setEndpointAttributes($device_token,"true",$customer_device->endpoint_arn);
                }
                $customer_device->update();
            }

            $customer = $customer_device->customer()->first();

            if($customer) {
                // get subscriber
                $api_subscriber = ApiSubscriber::with('userable')
                    ->where('client_id',$concept->client_id)
                    ->where('userable_id',$customer->id)->first();
            } else {

                // create a customer
                $customer = new Customer();
                $customer->first_name = $device_token;
                $customer->last_name = $device_id;
                $customer->mobile = $mobile;
                $customer->status = 'unverified';
                $customer->account_type = 'full';
                $customer->save();

                // attach the device to a customer
                $customer_device->customer_id = $customer->id;
                $customer_device->update();

                $customer->concepts()->attach($concept->id);

                $this->createApiSubscriber(
                    'customer',
                    $customer->id,
                    $device_token,
                    $device_token,
                    $this->getConcept($request)->client_id
                );

                $api_subscriber = ApiSubscriber::with('userable')
                    ->where('client_id',$concept->client_id)
                    ->where('userable_id',$customer->id)->first();
            }

            if(!$api_subscriber) {
                return response()->json(['error' => [
                    $this->responseArray(1005,404)
                ]], 404);
            }

            // subscribe to the correct arn
            $topic_arn = $customer_device->current_language == 'ar-sa' ? $topic_arn = $broadcast_arn['ar-sa'] : $topic_arn = $broadcast_arn['en-us'];
            $result = $this->sns->setEndpointSubscription($customer_device->endpoint_arn,'application',$topic_arn);

            if($result){
                $result = $result->get('SubscriptionArn');
                $customer_device->topic_subscription_arn = $result;
                $customer_device->update();
            }

            $payload = JWTFactory::setTTL(74649600)->sub($api_subscriber->id)->make();
            $token = $this->auth->encode($payload)->get();
            $customer_device->token = $token;

            return $this->response->item($customer_device, new CustomerDeviceTransformer, ['key' => 'customer-device']);
        }*/
    }

    public function publish(Request $request)
    {
        $concept = $this->getConcept($request);
        $application = $concept->applications()->first();

        $validator = app('validator')->make($request->all(),[
            //'email' => 'required|email',
            'message' => 'required',
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => [
                $this->responseArray(1056,400)
            ]], 400);
        }

        $message = json_decode(json_encode($request->input('message')),true);
        $title = json_decode(json_encode($request->input('title')),true);
        $message = array(
            'en-us' => $message['en-us'],
            'ar-sa' => $message['ar-sa'],
        );
        $title = array(
            'en-us' => $title['en-us'],
            'ar-sa' => $title['ar-sa'],
        );

        app('log')->debug('Notification Log: '.json_encode($message));

        //$this->sns->publishNotificationAndroid($application,$message,$title);
        //$this->sns->publishNotificationApple($application,$message,$title);
        $this->sns->publishNotificationToTopic($application,$message,$title);

        return response()->json([
            'data' => [
                'type' => 'notification',
                'attributes' => [
                    'status' => 'success',
                    'message' => ' notification sent'
                ]
            ]
        ]);
    }

    private function get_string_between($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    private function return_arn_endpoint($amazonCustomData, $arn, $device_token){

        $arn_endpoint = $this->sns->registerDevice($amazonCustomData, $arn, $device_token);

        if(is_array($arn_endpoint)){
            if(array_key_exists('error_msg',$arn_endpoint)){
                if (strpos($arn_endpoint['error_msg'], 'already exists') !== false) {
                    $parsed = $this->get_string_between($arn_endpoint['error_msg'], 'Endpoint', 'already');
                    $arn_endpoint = $this->get_string_between($parsed,'Endpoint ',' ');
                    app('log')->error("endpointARN: ".$arn_endpoint);
                }
            }
        }

        return $arn_endpoint;
    }
}