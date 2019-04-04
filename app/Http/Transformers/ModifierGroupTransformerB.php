<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Item;
use App\Api\V1\Models\ModifierGroup;


class ModifierGroupTransformerB extends ApiTransformer
{

    /*protected $defaultIncludes = [
        'modifiers'
    ];*/

    public function transform(ModifierGroup $model)
    {

        $relationships = [
            'modifiers' => $model->uri.'/modifiers'
        ];

        return $this->transformAll($model, $relationships, [
            'code' => $model->code,
            'display-order' => $model->display_order,
            'name' => $model->name,
            'image-uri' => $this->imageUri($model),
            'minimum' => $model->minimum,
            'maximum' => $model->maximum,
            'enabled' => $model->enabled
        ]);
    }

    /*public function includeModifiers(ModifierGroup $model)
    {
        $modifiers = $model->modifiers;

        return $this->collection($modifiers, new ModifierTransformer(),'modifier');
    }*/
}