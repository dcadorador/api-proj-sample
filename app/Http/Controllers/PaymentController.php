<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Transformers\PaymentTransformer;
use App\Api\V1\Models\Order;
use App\Api\V1\Models\Payment;
use App\Api\V1\Models\OrderOrderStatus;

class PaymentController extends ApiController
{

    public function index(Request $request, $order)
    {
        $order = Order::find($order);

        if($order === null){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        return $this->response->paginator($order->payments()->paginate($this->perPage), new PaymentTransformer(), ['key' => 'payment']);
    }

    public function store(Request $request, $order)
    {
        $order = Order::find($order);

        if($order === null){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $payment = $order->payments()->save(new Payment([
            'method' => $request->json('method'),
            'amount' => $request->json('amount'),
            'cash_presented' => $request->json('cash-presented')
        ]));

        // added for new order status
        $orderOrderStatus = new OrderOrderStatus();
        $orderOrderStatus->order_status_id = 25;
        $orderOrderStatus->order_id = $order->id;
        $orderOrderStatus->save();

        return $this->response->item($payment, new PaymentTransformer(), ['key' => 'payment']);
    }



}