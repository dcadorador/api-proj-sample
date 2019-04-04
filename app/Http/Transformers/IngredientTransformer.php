<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Ingredient;


class IngredientTransformer extends ApiTransformer
{

    public function transform(Ingredient $model)
    {

        $relationships = [];

        return $this->transformAll($model, $relationships, [
            'code' => $model->code,
            'display-order' => $model->display_order,
            'name' => $model->name,
            'image-uri' => $model->image_uri,
            'price' => $model->price,
            'calorie-count' => $model->calorie_count,
            'enabled' => $model->enabled
		]);    
	}
}