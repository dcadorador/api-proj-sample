<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\ItemModifier;


class OrderItemModifierTransformer extends ApiTransformer
{
    protected $defaultIncludes = [
        'modifierGroup'
    ];

    public function transform(ItemModifier $model)
    {

        $relationships = [
        ];

        return $this->transformAll($model, $relationships, [
            'id' => $model->modifier->id,
            //'modifier-id' => $model->modifier->id,
            'code' => $model->modifier->code,
            'name' => $model->modifier->name,
            'image-uri' => $model->modifier->image_uri,
            'quantity' => $model->quantity,
            'price' => $model->modifier->price,
            'modifier-group' => [
                'id' => $model->modifier->modifierGroup->id,
                'code' => $model->modifier->modifierGroup->code,
                'name' => $model->modifier->modifierGroup->name
            ]
        ]);

    }

    public function includeModifierGroup(ItemModifier $model)
    {
        $modifierGroup = $model->modifier->modifierGroup;

        return $this->item($modifierGroup, new ModifierGroupTransformerB, 'modifier-group');
    }
}