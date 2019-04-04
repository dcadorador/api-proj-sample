<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Item;
use App\Api\V1\Models\ItemModifierGroup;


class ItemModifierGroupTransformer extends ApiTransformer
{

    protected $defaultIncludes = [
        'modifiers'
    ];

    public function transform(ItemModifierGroup $model)
    {

        $relationships = [
            'modifiers' => $model->modifierGroup->uri.'/modifiers'
        ];

        $modifierGroup = $model->modifierGroup()->first();

        return $this->transformAll($modifierGroup, $relationships, [
            'code' => $modifierGroup->code,
            'display-order' => $model->display_order === null ? $modifierGroup->display_order : $model->display_order,
            'name' => $modifierGroup->name,
            'image-uri' => $this->imageUri($modifierGroup),
            'minimum' => $model->minimum === null ? $modifierGroup->minimum : $model->minimum,
            'maximum' => $model->maximum  === null ? $modifierGroup->maximum : $model->maximum,
            'enabled' => $model->enabled
        ]);
    }

    public function includeModifiers(ItemModifierGroup $model)
    {
        $modifierGroup = $model->modifierGroup()->first();
        $modifiers = $modifierGroup->modifiers->where('enabled',1);

        return $this->collection($modifiers, new ModifierTransformer(),'modifier');
    }
}