<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\ItemIngredient;


class ItemIngredientTransformer extends ApiTransformer
{

    public function transform(ItemIngredient $model)
    {

        $relationships = [
            'ingredient' => $model->ingredient->uri
        ];

        return $this->transformAll($model, $relationships, [
            'code' => $model->ingredient->code,
            'ingredient-id' => $model->ingredient->id,
            'display-order' => $model->display_order,
            'name' => $model->ingredient->name,
            'image-uri' => $model->ingredient->image_uri,
            'price' => $model->ingredient->price,
            'quantity' => $model->quantity,
            'enabled' => $model->enabled,
            'maximum-quantity' => $model->maximum_quantity,
            'minimum-quantity' => $model->minimum_quantity
		]);    
	}
}