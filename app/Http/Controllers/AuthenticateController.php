<?php 

namespace App\Api\V1\Controllers;

use App\User;
use Dingo\Api\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Api\V1\Models\ApiSubscriber;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Api\V1\Models\Customer;
use App\Api\V1\Models\Concept;
use JWTFactory;
use Carbon\Carbon;
use App\Api\V1\Transformers\CustomerTransformer;
use App\Api\V1\Helpers\Guzzle6HttpClient;

class AuthenticateController extends ApiController
{
    private $provider;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    public function backend(Request $request)
    {
        $provider = $request->json('provider');
        $concept_id = $request->header('Solo-Concept',null);
        $provider_type = null;

        // check if provider input in providers
        if(in_array($provider, [1, 2, 3, 4, 5, 6])) {
            $this->setProvider($provider);
        }

        switch ($provider) {

            // Google
            case 1:
                //
                $verify = new \Google_AccessToken_Verify();
                $result = $verify->verifyIdToken($request->json('token'));
                app('log')->debug('Google Authentication Response: '.json_encode($result));

                if ($result === false) {
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);
                }

                $subscriber = $this->google_data($result,$request,$concept_id);
                $type = $subscriber->userable_type;
                $payload = JWTFactory::setTTL(74649600)->sub($subscriber->id)->make();
                $token = $this->auth->encode($payload)->get();
                $user = Customer::where('id',$subscriber->userable_id)->first();
                $provider_type = 'google';
                break;

            // Twitter
            case 2:
                //
                $twitter_def_ckey = null;
                $twitter_def_csec = null;

                if($concept_id and $concept_id !='') {
                    $twitter_key = config('oauth_token.twitter')[$concept_id]['twitter_def_ckey'];
                    $twitter_sec = config('oauth_token.twitter')[$concept_id]['twitter_def_csec'];
                }

                $twitter = new TwitterOAuth($twitter_key,$twitter_sec,$request->json('token'),$request->json('secret'));
                $twitter->setTimeouts(300, 300);
                $result = $twitter->get("account/verify_credentials");

                app('log')->debug('Twitter Authentication Reponse: '.json_encode($result));

                if (isset($result->errors)) {
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);
                }

                $subscriber = $this->twitter_data($result, $request, $concept_id);
                $type = $subscriber->userable_type;
                $payload = JWTFactory::setTTL(74649600)->sub($subscriber->id)->make();
                $token = $this->auth->encode($payload)->get();
                $user = Customer::where('id',$subscriber->userable_id)->first();
                $provider_type = 'twitter';
                break;

            // adding FB login
            case 4:
                $fb_client_id = null;
                $fb_client_sec = null;
                $fb_api_ver = null;

                if($concept_id and $concept_id !='') {
                    $fb_client_id = config('oauth_token.facebook')[$concept_id]['fb_app_id'];
                    $fb_client_sec = config('oauth_token.facebook')[$concept_id]['fb_app_sec'];
                    $fb_api_ver = config('oauth_token.facebook')[$concept_id]['fb_api_ver'];
                }

                $client = new \GuzzleHttp\Client;
                $fb = new \Facebook\Facebook([
                    'app_id' => $fb_client_id,
                    'app_secret' => $fb_client_sec,
                    'default_graph_version' => $fb_api_ver,
                    'http_client_handler' => new Guzzle6HttpClient($client),
                ]);

                try {
                    // Get the \Facebook\GraphNodes\GraphUser object for the current user.
                    // If you provided a 'default_access_token', the '{access-token}' is optional.
                    $response = $fb->get('/me', $request->json('token'));
                    $response = $response->getDecodedBody();
                } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                    // When Graph returns an error
                    app('log')->debug('Graph returned an error: ' . $e->getMessage());
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);

                } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                    // When validation fails or other local issues
                    app('log')->debug('Facebook SDK returned an error: ' . $e->getMessage());
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);

                }

                /*if(!$response) {
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);
                }*/

                $subscriber = $this->fb_data($response, $request, $concept_id);
                $type = $subscriber->userable_type;
                $payload = JWTFactory::setTTL(74649600)->sub($subscriber->id)->make();
                $token = $this->auth->encode($payload)->get();
                $user = Customer::where('id',$subscriber->userable_id)->first();
                $provider_type = 'FB';
                break;

            // added instagram login
            case 5:
                $token = $request->json('token');
                $subscriber = $this->getInstagramUserProfile($token,$concept_id);

                if(!$subscriber) {
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);
                }

                $type = $subscriber->userable_type;
                $payload = JWTFactory::setTTL(74649600)->sub($subscriber->id)->make();
                $token = $this->auth->encode($payload)->get();
                $user = Customer::where('id',$subscriber->userable_id)->first();
                $provider_type = 'instagram';
                break;

            // added linkedin login
            case 6:
                $token = $request->json('token');
                $subscriber = $this->getLinkedProfile($token,$concept_id);

                if(!$subscriber) {
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);
                }

                $type = $subscriber->userable_type;
                $payload = JWTFactory::setTTL(74649600)->sub($subscriber->id)->make();
                $token = $this->auth->encode($payload)->get();
                $user = Customer::where('id',$subscriber->userable_id)->first();
                $provider_type = 'linkedin';
                break;

            case 3:
            default:
                // grab credentials from the request
                //TODO authenticate on "username" rather than "email" to validate devices as well as people
                $concept = Concept::find($concept_id);
                $credentials = $request->only('username', 'password');

                if($concept) {
                    $credentials['client_id'] = $concept->client_id;
                }

                try {
                    // attempt to verify the credentials and create a token for the user
                    if (! $token = $this->auth->attempt($credentials)) {
                        // add the concept for burgerizzr
                        if($concept_id == 5) {
                            return response()->json(['error' => [
                                $this->responseArray(1050,400)
                            ]], 400);
                        }
                        return response()->json(['error' => [
                            $this->responseArray(1001,401)
                        ]], 401);
                    }
                    $subscriber = ApiSubscriber::with('userable')
                        ->where('username',$request->json('username'));
                    if($concept) {
                        $subscriber->where('client_id',$concept->client_id);
                    }
                    $subscriber = $subscriber->first();
                    $type = $subscriber->userable_type;
                    $user = $subscriber->userable()->first();
                } catch (JWTException $e) {
                    // something went wrong whilst attempting to encode the token
                    return response()->json(['error' => [
                        $this->responseArray(1001,401)
                    ]], 401);
                }
                $provider_type = 'email/password';
                break;
        }

        // added this for the log in that
        if(($concept_id and $concept_id !='') and $provider == 3){

            $concept = Concept::find($concept_id);

            if (!$concept) {
                return response()->json(['error' => [
                    $this->responseArray(1014,404)
                ]], 404);
            }

            if(!$user->concepts->contains($concept->id)){
                return response()->json(['error' => [
                    $this->responseArray(1001,401)
                ]], 401);
            }
        }

        $concepts = [];
        foreach ($user->concepts as $concept) {
            $concepts[] = ['id' => $concept->id,
                           'label' => $concept->label,
                           'links' => [
                                        'self' => $concept->uri
                                      ]
                          ];
        }


        // added the logging of customer provider login method
        app('log')->info('Customer ID: '.$user->id.' - Logged in using Provider: '.$provider_type);

        return response()->json([
            'data' => [
                'type' => $type,
                'id' => $user->id,
                'attributes' => [
                    'name' => ucwords($user->first_name).' '.ucwords($user->last_name),
                    'status' => $user->status,
                    'token' => $token,
                    'concepts' => $concepts
                ],
                'links' => [
                    'self' => $user->uri
                ],
                'relationships' => [
                    'concepts' => [
                        'links' => [
                            'self' => $user->uri.'/concepts' 
                        ]
                    ]
                ]
            ]
        ]);

    }

    private function google_data($result,$request, $concept_id)
    {
        app('log')->debug('Google Data: '.json_encode($result));
        $gu = (object)$result;
        $concept = Concept::find($concept_id);

        if(
            !$subscriber = ApiSubscriber::where('username',$gu->sub)
                ->where('client_id',$concept->client_id)
                ->with('userable')->first()
        ) {

            $code = $this->generateCode();
            $customer = new Customer();
            $customer->first_name = $gu->given_name;
            $customer->sms_code = $code;
            $customer->last_name = $gu->family_name;
            $customer->email = $gu->email;
            $customer->mobile = null;
            $customer->status = 'unverified';
            $customer->account_type = 'full';
            $customer->provider_id = $this->provider;
            $customer->save();

            if($concept_id) {
                $customer->concepts()->attach($concept_id);
            }


            $this->createApiSubscriber(
                'customer',
                $customer->id,
                $gu->sub,
                md5($gu->sub),
                $concept->client_id
            );

            $subscriber = ApiSubscriber::with('userable')
                    ->where('client_id',$concept->client_id)
                    ->where('userable_id',$customer->id)->first();
        } /*else {
            $customer = $subscriber->userable()->first();
            if($customer) {
                if($concept_id) {
                    if(!$customer->concepts->contains($concept_id)){
                        $customer->concepts()->attach($concept_id);
                    }
                }
            }
            return $subscriber;
        }*/
        return $subscriber;
    }


    private function twitter_data($result,$request, $concept_id)
    {
        app('log')->debug('Twitter Data: '.json_encode($result));
        $tu = (object)$result;
        $concept = Concept::find($concept_id);

        if(
        !$subscriber_1 = ApiSubscriber::where('username',$tu->id)
            ->where('client_id',$concept->client_id)
            ->with('userable')->first()
        ) {
            $code = $this->generateCode();
            $customer1 = new Customer();
            $name = $this->split_name($tu->name);
            $customer1->first_name = $name[0];
            $customer1->sms_code = $code;
            $customer1->last_name = $name[1];
            $customer1->email = null;
            $customer1->mobile = null;
            $customer1->status = 'unverified';
            $customer1->account_type = 'full';
            $customer1->provider_id = $this->provider;
            $customer1->save();

            if($concept_id) {
                $customer1->concepts()->attach($concept_id);
            }

            $this->createApiSubscriber(
                'customer',
                $customer1->id,
                $tu->id,
                md5($tu->id),
                $concept->client_id
            );

            $subscriber_1 =  ApiSubscriber::with('userable')
                ->where('client_id',$concept->client_id)
                ->where('userable_id',$customer1->id)->first();
        } /*else {
            $customer1 = $subscriber_1->userable()->first();
            if($customer1) {
                if($concept_id) {
                    if(!$customer1->concepts->contains($concept_id)){
                        $customer1->concepts()->attach($concept_id);
                    }
                }
            }
            return $subscriber_1;
        }*/
        return $subscriber_1;
    }

    private function fb_data($result,$request,$concept_id)
    {
        app('log')->debug('FB Data: '.json_encode($result));
        $fu = (object)$result;
        $concept = Concept::find($concept_id);
        if (
            !$subscriber_2 = ApiSubscriber::where('username',$fu->id)
                ->where('client_id',$concept->client_id)
                ->with('userable')->first()
        ) {
            $code = $this->generateCode();
            $customer2 = new Customer();
            $name = $this->split_name($fu->name);
            $customer2->first_name = $name[0];
            $customer2->sms_code = $code;
            $customer2->last_name = $name[1];
            $customer2->email = null;
            $customer2->mobile = null;
            $customer2->status = 'unverified';
            $customer2->account_type = 'full';
            $customer2->provider_id = $this->provider;
            $customer2->save();

            if($concept_id) {
                $customer2->concepts()->attach($concept_id);
            }

            $this->createApiSubscriber(
                'customer',
                $customer2->id,
                $fu->id,
                md5($fu->id),
                $concept->client_id
            );

            $subscriber_2 = ApiSubscriber::with('userable')
                ->where('client_id',$concept->client_id)
                ->where('userable_id',$customer2->id)->first();
        }

        return $subscriber_2;
    }

    public function forgotPassword(Request $request)
    {
        $username = trim($request->json('username'));

        // retrieve the concept id
        $concept_id = $request->header('Solo-Concept',null);
        // get the concept
        $concept = Concept::find($concept_id);

        if (!$concept) {
            return response()->json(['error' => [
                $this->responseArray(1014,404)
            ]], 404);
        }

        // check if there is an api subscriber with that username
        $subscriber = ApiSubscriber::where('username',$username)
            ->where('client_id',$concept->client_id)
            ->where('userable_type','customer')
            ->first();

        if(!$subscriber){
            return response()->json(['error' => [
                $this->responseArray(1005,404)
            ]], 404);
        }

        $customer = $subscriber->userable()->first();

        if(!$customer->concepts->contains($concept->id)){
            return response()->json(['error' => [
                $this->responseArray(1014,404)
            ]], 404);
        }

        // update subscriber password
        $code = md5($username.' '.'!@#$%^&*()_+'.' '.Carbon::now()->toAtomString());
        $newPassword = substr($code, 0, 8);
        $subscriber->password = $newPassword;
        $subscriber->update();

        // data for the email
        $data = [
            'name' => ucwords($customer->first_name).' '.ucwords($customer->last_name),
            'password' => $newPassword,
            'client' => ucwords($concept->label)
        ];

        app('mailer')->send('password', $data, function ($message) use($customer,$concept) {
            $message->subject('Forgot Password');
            $message->to($customer->email);
            $message->from($concept->feedback_email,ucwords($concept->label));
        });

        return $this->response->item($customer,new CustomerTransformer,['key' => 'customer']);
    }

    private function getInstagramUserProfile($token,$concept_id)
    {
        $url = 'https://api.instagram.com/v1/users/self/?access_token=' . $token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $data = json_decode(curl_exec($ch), true);
        app('log')->debug('Instagram Data: '.json_encode($data));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // if other than 200 return error
        if($http_code != 200) {
            return false;
        }

        $iu = (object)$data['data'];
        $concept = Concept::find($concept_id);

        if (
        !$subscriber_instagram = ApiSubscriber::where('username',$iu->id)
            ->where('client_id',$concept->client_id)
            ->with('userable')->first()
        ) {
            $code = $this->generateCode();
            $customer_ins = new Customer();
            $name = $this->split_name($iu->full_name);
            $customer_ins->first_name = $name[0];
            $customer_ins->sms_code = $code;
            $customer_ins->last_name = $name[1];
            $customer_ins->email = null;
            $customer_ins->mobile = null;
            $customer_ins->status = 'unverified';
            $customer_ins->account_type = 'full';
            $customer_ins->provider_id = $this->provider;
            $customer_ins->save();

            if($concept_id) {
                $customer_ins->concepts()->attach($concept_id);
            }

            $this->createApiSubscriber(
                'customer',
                $customer_ins->id,
                $iu->id,
                md5($iu->id),
                $concept->client_id
            );

            $subscriber_instagram = ApiSubscriber::with('userable')
                ->where('client_id',$concept->client_id)
                ->where('userable_id',$customer_ins->id)->first();
        }

        return $subscriber_instagram;
    }

    private function getLinkedProfile($token, $concept_id)
    {
        $url = 'https://api.linkedin.com/v1/people/~?format=json';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
        ));

        $data = json_decode(curl_exec($ch), true);
        app('log')->debug('Instagram Data: '.json_encode($data));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // if other than 200 return error
        if($http_code != 200) {
            return false;
        }

        $lu = (object)$data;
        $concept = Concept::find($concept_id);

        if (
        !$subscriber_linkedin = ApiSubscriber::where('username',$lu->id)
            ->where('client_id',$concept->client_id)
            ->with('userable')->first()
        ) {
            $code = $this->generateCode();
            $customer_linkedin = new Customer();
            //$name = $this->split_name($iu->full_name);
            $customer_linkedin->first_name = $lu->firstName;
            $customer_linkedin->sms_code = $code;
            $customer_linkedin->last_name = $lu->lastName;
            $customer_linkedin->email = null;
            $customer_linkedin->mobile = null;
            $customer_linkedin->status = 'unverified';
            $customer_linkedin->account_type = 'full';
            $customer_linkedin->provider_id = $this->provider;
            $customer_linkedin->save();

            if($concept_id) {
                $customer_linkedin->concepts()->attach($concept_id);
            }

            $this->createApiSubscriber(
                'customer',
                $customer_linkedin->id,
                $lu->id,
                md5($lu->id),
                $concept->client_id
            );

            $subscriber_linkedin = ApiSubscriber::with('userable')
                ->where('client_id',$concept->client_id)
                ->where('userable_id',$customer_linkedin->id)->first();
        }

        return $subscriber_linkedin;
    }

    private function setProvider($provider = null)
    {
        $this->provider = $provider;
    }

}