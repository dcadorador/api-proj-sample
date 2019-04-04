<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Order;
use App\Api\V1\Models\Topic;
use App\Api\V1\Transformers\TopicTransformer;
use Illuminate\Http\Request;

class OrderRatingsController extends ApiController
{
    public function getTopics(Request $request, $order)
    {
        $order = Order::find($order);

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $orderRatingTopics = $order->orderRatings()->pluck('topic_id')->toArray();

        $topics = Topic::whereIn('id', array_unique($orderRatingTopics))->paginate($this->perPage);

        return $this->response->paginator($topics, new TopicTransformer, ['key' => 'topics']);
    }
}