<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Transformers\OrderRatingTransformer;
use App\Api\V1\Models\Feedback;

class FeedbackRatingsController extends ApiController
{
    public function index($feedback) {

        $feedback = Feedback::find($feedback);

        return $this->response->paginator($feedback->ratings()->paginate($this->perPage), new OrderRatingTransformer, ['key' => 'ratings']);
    }
}