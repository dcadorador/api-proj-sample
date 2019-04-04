<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\BundledCategory;


class BundledCategoryTransformer extends ApiTransformer
{

    public function transform(BundledCategory $model)
    {

        $relationships = [
            'category' => $model->category->uri,
            'default-item' => $model->defaultItem->uri
        ];

        return $this->transformAll($model, $relationships, [
            'code' => $model->category->code,
            'name' => $model->category->name,
            'default-item-id' => $model->defaultItem->id
        ]);
	}
}