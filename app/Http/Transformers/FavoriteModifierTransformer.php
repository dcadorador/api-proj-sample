<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Favorite;
use App\Api\V1\Models\FavoriteItemModifier;
use App\Api\V1\Models\Modifier;
use Illuminate\Support\Facades\Request;

class FavoriteModifierTransformer extends ApiTransformer
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'modifierGroup'
    ];


    public function transformCollection(array $items)
    {
        return array_map([$this, 'transform'], $items);
    }

    public function transform(FavoriteItemModifier $model)
    {
        $lang = Request::header('Accept-Language') ? Request::header('Accept-Language') : 'ar-sa';

        $relationships = [
        ];

        return $this->transformAll($model, $relationships, [
            'id' =>  $model->modifier->id,
            'modifier-id' => $model->modifier->id,
            'name' => $model->modifier->translate($lang)->name,
            'quantity' => $model->quantity,
            //'price' => ((int)$model->modifier->price * (int)$model->quantity),
            'price' => (int)$model->modifier->price,
            'modifier-group' => [
                'id' => $model->modifier->modifierGroup->id,
                'code' => $model->modifier->modifierGroup->code,
                'name' => $model->modifier->modifierGroup->name
            ]
        ]);    
    }


    /**
     * Include Modifiers
     *
     * @return League\Fractal\ItemResource
     */
    public function includeModifierGroup(FavoriteItemModifier $model)
    {
        $modifierGroup = $model->modifier->modifierGroup;

        return $this->item($modifierGroup, new ModifierGroupTransformerB, 'modifier-group');
    }
}