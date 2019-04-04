<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Integration;
use App\Api\V1\Models\Order;
use App\Api\V1\Models\OrderRating;
use App\Api\V1\Services\IntegrationService;
use App\Api\V1\Transformers\FeedbackTransformer;
use Illuminate\Http\Request;
use App\Api\V1\Models\Feedback;
use App\Api\V1\Helpers\AuthHelper;
use Illuminate\Support\Facades\Log;

class FeedbackController extends ApiController
{
    public function sendFeedback(Request $request)
    {
        $validator = app('validator')->make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'telephone' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => [
                $this->responseArray(1030,400)
            ]], 400);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $telephone = $request->input('telephone');
        $subject = $request->input('subject');
        $body = $request->input('body');
        $feedback_email = $this->getConcept($request)->feedback_email;
        $customer = null;

        if ($subscriber = AuthHelper::getAuthenticatedUser()) {
            $customer = $subscriber->userable()->first();
        }

        // format user agent
        $device = null;
        $user_agent = $request->header('user_agent');
        if(!is_object(json_decode($user_agent))) {
            $user_agent = str_replace("'",'"',$user_agent);
        }
        $app_version = (array)json_decode($user_agent);
        if (array_key_exists('Device',$app_version)) {
            $device = $app_version['Device'];
            unset($app_version['Device']);
        }

        $feedback = new Feedback();
        $feedback->name = $name;
        $feedback->email = $email;
        $feedback->telephone = $telephone;
        $feedback->subject = $subject;
        $feedback->body = $body;
        $feedback->concept_id = $this->getConcept($request)->id;
        $feedback->customer_id = $customer ? $customer->id : null;
        $feedback->device = $device;
        $feedback->user_agent = json_encode($app_version);
        $feedback->save();

        $concept_id = $this->getConcept($request)->id;

        if($request->has('ratings')) {
            $order = Order::find($request->input('order_id'));

            if(!$order){
                return response()->json(['error' => [
                    $this->responseArray(1022,404)
                ]], 404);
            }

            foreach ($request->input('ratings') as $key => $value) {
                $rating = $order->orderRatings()->save(new OrderRating([
                    'rating' => $value['rating'],
                    'feedback_id' => $feedback->id,
                    'topic_id' => $value['topic_id']
                ]));
            }
        }

        // Added this since this is for Hamburgini Concept, can be removed.
        if($concept_id == 1) {
            $integration = Integration::where('concept_id', $concept_id)->where('type', 'feedback')->first();
            $integrationService = new IntegrationService($this->getConcept($request),
                $integration);

            $integrationService->getHappyFoxFeedback([
                'subject' => $subject,
                'text' => $body,
                'email' => $email,
                'name' => $name
            ]);
        }

        $data = [
            'name'=> $name,
            'telephone'=> $telephone,
            'email'=>  $email,
            'subject'=> $subject,
            'body'=> $body,
            'device' => json_encode($app_version)
        ];

        if ($request->hasFile('image')) {

            $validator = app('validator')->make($request->all(), [
                'image' => 'max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => [
                    $this->responseArray(1029,400)
                ]], 400);
            }

            $feedback->image_uri = $this->saveUploadedFile($request, 'image');
            $feedback->save();

            // get file and name
            $image = $request->file('image');
            $name = $image->getClientOriginalName();
            $mime = $image->getClientMimeType();
            // move file for mailing
            $image->move(storage_path(),$name);

            // send mail
            app('mailer')->send('feedback', $data, function ($message) use($subject,$feedback_email,$name,$email,$mime) {
                $message->subject($subject);
                $message->to($feedback_email);
                $message->from($feedback_email,'Customer Feedback');
                $message->attach(storage_path().'/'.$name,['mime' => $mime]);
            });
            app('log')->info('SENDING Feedback email with FEEDBACK ID: '.$feedback->id);
            unlink(storage_path().'/'.$name);
        } else {

            // save image if the link is a url not an image
            if($request->has('image')) {
                if($feedback) {
                    $feedback->image_uri = $request->input('image');
                    $feedback->update();
                }

                if($rating) {
                    $rating->image = $request->input('image');
                    $rating->update();
                }

            }

            app('mailer')->send('feedback', $data, function ($message) use($subject,$feedback_email,$name,$email) {
                $message->subject($subject);
                $message->to($feedback_email);
                $message->from($feedback_email,'Customer Feedback');
            });
            app('log')->info('SENDING Feedback email with FEEDBACK ID: '.$feedback->id);
        }

        return $this->response->item($feedback, new FeedbackTransformer(),['key' => 'feedback']);
    }

    public function index(Request $request) {

        $concept = $this->getConcept($request);

        $feedbacks = Feedback::where('concept_id',$concept->id)
            ->orderBy('created_at','DESC')
            ->paginate($this->perPage);

        return $this->response->paginator($feedbacks, new FeedbackTransformer(),['key' => 'feedback']);
    }
}