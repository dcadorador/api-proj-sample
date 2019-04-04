<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Menu;


class MenuTransformer extends ApiTransformer
{

    public function transform(Menu $model)
    {

        $relationships = [
            'concept' => $model->concept->uri,
            'categories' => $model->uri.'/categories'
        ];

        return $this->transformAll($model, $relationships, [
            'code' => $model->code,
            'label' => $model->label,
            'key' => $model->key
        ]);

	}
}