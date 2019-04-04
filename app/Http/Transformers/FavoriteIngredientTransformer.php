<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Favorite;
use App\Api\V1\Models\FavoriteItemIngredient;
use App\Api\V1\Models\ItemModifier;


class FavoriteIngredientTransformer extends ApiTransformer
{
    public function transformCollection(array $items)
    {
        return array_map([$this, 'transform'], $items);
    }

    public function transform(FavoriteItemIngredient $model)
    {

        $relationships = [
        ];

        $data = [
                'id' => $model->ingredient->id,
                'code' => $model->ingredient->ingredient->code,
                'ingredient-id' => $model->ingredient->ingredient->id,
                'display-order' => $model->ingredient->display_order,
                'name' => $model->ingredient->ingredient->name,
                'image-uri' => $model->ingredient->ingredient->image_uri,
                'price' => $model->ingredient->ingredient->price,
                'quantity' => $model->quantity,
                'maximum-quantity' => $model->ingredient->maximum_quantity,
                'minimum-quantity' => $model->ingredient->minimum_quantity
        ];

        return $this->transformAll($model, $relationships, $data);
    }

}