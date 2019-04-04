<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Export;


class ExportTransformer extends ApiTransformer
{

    public function transform(Export $model)
    {

        $relationships = [
            'concept' => $model->concept->uri
        ];

        $data = [
            'type' => $model->type,
            'csv-uri' => $model->csv_uri
        ];

        return $this->transformAll($model, $relationships, $data);
    }
}