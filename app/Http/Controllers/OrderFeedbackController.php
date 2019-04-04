<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Order;
use App\Api\V1\Models\OrderFeedback;
use App\Api\V1\Models\OrderRating;
use App\Api\V1\Transformers\OrderFeedbackTransformer;
use Illuminate\Http\Request;

class OrderFeedbackController extends ApiController
{
    public function store(Request $request, $order)
    {
        $order = Order::find($order);

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $order->orderRating()->save(new OrderRating([
            'rating' => $request->json('rating', null),
            'subject' => $request->json('subject', null),
            'topic_id' => $request->json('topic_id')
        ]));

        return $this->response->item($order->orderFeedback, new OrderFeedbackTransformer, ['key' => 'order-feedback']);
    }
}