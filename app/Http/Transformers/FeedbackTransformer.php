<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Feedback;
use App\Api\V1\Models\OrderRating;


class FeedbackTransformer extends ApiTransformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
        'ratings'
    ];

    public function transform(Feedback $model)
    {
        $relationships = [
            'concept' => $model->concept->uri,
            'ratings' => $model->uri . '/ratings'
        ];

        $data = [
            'name' => $model->name,
            'email' => $model->email,
            'telephone' => $model->telephone,
            'subject' => $model->subject,
            'body' => $model->body,
            'image-uri' => $model->image_uri,
            'user-agent' => $model->user_agent
        ];

        if ($model->customer) {
            $relationships = array_add($relationships, 'customer', $model->customer->uri);
            $data = array_add($data, 'customer-id', $model->customer->id);
        }

        return $this->transformAll($model, $relationships, $data);
    }

    public function includeRatings(Feedback $model)
    {
        $ratings = $model->ratings;

        return $this->collection($ratings, new OrderRatingTransformer, 'order-rating');
    }
}