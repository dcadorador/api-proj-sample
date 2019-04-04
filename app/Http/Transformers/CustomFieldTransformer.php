<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\CustomField;


class CustomFieldTransformer extends ApiTransformer
{

    public function transform(CustomField $model)
    {

        $relationships = [];

        $data = [
            'label' => $model->label,
            'type' => $model->type
        ];

        return $this->transformAll($model, $relationships, $data);
	}
}