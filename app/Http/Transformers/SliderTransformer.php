<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Slider;

class SliderTransformer extends ApiTransformer
{

    public function transform(Slider $model)
    {

        $relationships = [
            'concept' => $model->concept->uri,
        ];

        $data = [
            'label' => $model->label,
            'status' => $model->status
        ];

        return $this->transformAll($model, $relationships, $data);
    }

}