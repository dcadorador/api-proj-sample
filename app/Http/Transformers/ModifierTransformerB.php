<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Modifier;


class ModifierTransformerB extends ApiTransformer
{

    public function transform(Modifier $model)
    {

        $relationships = [];

        if ($model->locations) {
            $relationships = array_add($relationships, 'locations', $model->uri.'/locations');
        }

        return $this->transformAll($model, $relationships, [
            'code' => $model->code,
            'display-order' => $model->display_order,
            'name' => $model->name,
            'image-uri' => $this->imageUri($model),
            'price' => $model->price,
            'calorie-count' => $model->calorie_count,
            'minimum' => $model->minimum,
            'maximum' => $model->maximum,
            'enabled' => $model->enabled
        ]);
    }

}