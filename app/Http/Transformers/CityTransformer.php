<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\City;


class CityTransformer extends ApiTransformer
{

    public function transform(City $model)
    {

        $relationships = [];

        return $this->transformAll($model, $relationships, [
            'name' => $model->name
        ]);
    }
}