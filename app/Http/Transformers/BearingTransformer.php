<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Bearing;


class BearingTransformer extends ApiTransformer
{

    public function transform(Bearing $model)
    {

        $relationships = [
            'employee' => $model->employee->uri,
        ];

        return $this->transformAll($model, $relationships, [
            'lat' => $model->lat,
            'long' => $model->long
        ]);
    }
}