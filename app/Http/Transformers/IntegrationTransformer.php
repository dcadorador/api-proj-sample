<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Integration;



class IntegrationTransformer extends ApiTransformer
{

    public function transform(Integration $model)
    {
        $relationships = [
        ];

        return $this->transformAll($model, $relationships, [
            'type' => $model->type,
            'provider' => $model->provider,
            'options' => $model->options
        ]);

    }

}