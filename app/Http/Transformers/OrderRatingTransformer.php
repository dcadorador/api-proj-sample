<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\OrderRating;


class OrderRatingTransformer extends ApiTransformer
{
    protected $availableIncludes = [
        'order',
        'feedback',
        'topic'
    ];

    public function transform(OrderRating $model)
    {
        $relationships = [];

        return $this->transformAll($model, $relationships, [
            'rating' => $model->rating
        ]);
    }

    public function includeOrder(OrderRating $model)
    {
        $order = $model->order;
        if($order) {
            return $this->item($order, new OrderTransformer, 'order');
        }
    }

    public function includeFeedback(OrderRating $model)
    {
        $feedback = $model->feedback;
        if($feedback) {
            return $this->item($feedback, new FeedbackTransformer, 'feedback');
        }
    }

    public function includeTopic(OrderRating $model)
    {
        $topic = $model->topic;
        if($topic) {
            return $this->item($topic, new TopicTransformer, 'topic');
        }
    }
}