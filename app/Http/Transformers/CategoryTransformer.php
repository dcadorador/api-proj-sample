<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Category;


class CategoryTransformer extends ApiTransformer
{

    public function transform(Category $model)
    {

        $relationships = [
            'menu' => $model->menu->uri,
            'items' => $model->uri.'/items'
        ];

        return $this->transformAll($model, $relationships, [
            'code' => $model->code,
            'display-order' => $model->display_order,
            'name' => $model->name,
            'description' => $model->description,
            'image-uri' => $model->image_uri,
            'enabled' => $model->enabled
        ]);

	}
}