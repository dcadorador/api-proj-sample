<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Helpers\SupportMailer;
use App\Api\V1\Models\Checkout;
use App\Api\V1\Models\Customer;
use App\Api\V1\Models\Integration;
use App\Api\V1\Models\Order;
use App\Api\V1\Models\OrderOrderStatus;
use App\Api\V1\Models\Payment;
use App\Api\V1\Services\HyperPayService;
use App\Jobs\NewOrderJob;
use Illuminate\Http\Request;

class HyperPayController extends ApiController
{
    /**
     * Checkout items
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $concept_id = $this->getConcept($request)->id;

        $integration = Integration::where('concept_id', $concept_id)
            ->where('type', 'payment')->first();

        $email = $request->input('email');

        $customer = Customer::where('email', $email)
            ->first();

        $order = $customer->orders()->whereNotNull('code')->latest()->first();

        $options = [
            'amount' => $request->input('amount'),
            'merchantTransactionId' => $order->code,
            'customer.email' => $email,
            'currency' => 'SAR',
            'paymentType' => 'DB'
        ];

        $customer = Customer::where('email', $email)
            ->first();

        $hyperpayService = new HyperPayService($integration, $options, 'checkouts');

        $response = $hyperpayService->sendCheckout();

        $order->orderCheckouts()->save(new Checkout([
            'customer_id' => $customer->id,
            'checkout_id' => $response->id
        ]));

        return response()->json([
            'checkoutId' => $response->id
        ]);
    }

    /**
     * Get status of payment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getPaymentStatus(Request $request)
    {
        $checkoutId = $request->input('checkout_id');

        $checkout = Checkout::where('checkout_id', $checkoutId)
            ->latest()
            ->first();

        $concept_id = $this->getConcept($request)->id;

        $integration = Integration::where('concept_id', $concept_id)
            ->where('type', 'payment')->first();

        $hyperpayService = new HyperPayService($integration, []);

        $url = "https://test.oppwa.com/v1/checkouts/" . $checkout->checkout_id . "/payment";
        $url .= "?authentication.userId=" . $hyperpayService->getAuthUserId();
        $url .= "&authentication.password=" . $hyperpayService->getAuthPassword();
        $url .= "&authentication.entityId=" . $hyperpayService->getAuthEntity();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseData = curl_exec($ch);

        if(curl_errno($ch)) {
            return curl_error($ch);
        }

        curl_close($ch);

        return response()->json($responseData);
    }

    public function webhook(Request $request)
    {
        app('log')->debug('RECEIVED CALL BACK FROM HYPERPAY');

        // get all data passed by foodics
        $d = json_decode(json_encode($request->all()),true);

        // log in the logfile
        app('log')->info(print_r($d,true));

        $mailer = new SupportMailer();

        $code = $d['payload']['merchantTransactionId'];
        // get order
        $order = Order::where('code',$code)
            ->first();

        if(!$order || $d['type'] === 'REGISTRATION') {
            app('log')->info('PAYMENT TYPE WRONG:'.json_encode($d,true));

            return $this->response->noContent();
        }

        // transaction failure status codes
        $failedStatusCodes = preg_match('/^(800\.[17]00|800\.800\.[123])/', $d['payload']['result']['code']);

        if((bool) $failedStatusCodes) {
            app('log')->info('HYPERPAY TRANSACTION FAILED:'.json_encode($d,true));

            // return ok
            return response()->json(['ok' => [
                'status' => true,
                'message' => 'Transaction failed'
            ]], 200);
        }

        switch($d['payload']['result']['code']){
            case '000.000.000':
                app('log')->info('TRANSACTION SUCCEED:'.json_encode($d,true));

                // send payment email
                $mailer->sendPaymentMail($order,$d['payload']);

                $status = new OrderOrderStatus();
                $status->order_id = $order->id;
                $status->order_status_id = 35;
                $status->save();

                // dispatch order
                dispatch(new NewOrderJob($order));

                // return ok
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Transaction succeeded'
                ]], 200);

                break;

            case '000.000.100':
                app('log')->info('SUCCESSFUL REQUEST:'.json_encode($d,true));

                // return ok
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Successful Request'
                ]], 200);

                break;

            case '000.100.110':
                app('log')->info('Request successfully processed in \'Merchant in Integrator Test Mode\':'.json_encode($d,true));

                // return ok
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Request successfully processed in \'Merchant in Integrator Test Mode\''
                ]], 200);
                break;

            case '000.200.000':

                app('log')->info('Transaction pending: '.json_encode($d,true));

                // return ok
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Transaction pending'
                ]], 200);

                break;
            case '000.200.100':

                app('log')->info('successfully created checkout:'.json_encode($d,true));

                // return ok
                return response()->json(['ok' => [
                    'status' => true,
                    'message' => 'Successfully created checkout'
                ]], 200);

                break;
        }

        $order->payments()->save(new Payment([
            'method' => 'card',
            'amount' => ((int)$d['payload']['amount']),
            'payment_reference_number' => $d['payload']['id'],
            'status' => 'success'
        ]));

        return $this->response->noContent();
    }
}