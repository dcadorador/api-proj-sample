<?php
/**
 * Added because there is an error when default includes
 */

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\ItemOrder;
use App\Api\V1\Models\Item;
use App\Api\V1\Services\HamburginiMealService;

class OrderItemTransformerB extends ApiTransformer
{

    protected $availableIncludes = [
        'modifiers',
        'ingredients'
    ];

    public function transform(ItemOrder $model)
    {

        $relationships = [
            'order' => $model->order->uri
        ];

        $service = new HamburginiMealService();
        $num_of_mod = $service->getNumberOfSizeMods($model);

        $data = [
            'id' => $model->id,
            'item-id' => $model->item->id,
            'code' => $model->item->code,
            'name' => $model->item->name,
            'image-uri' => $model->item->image_uri,
            'quantity' => $model->quantity,
            'current-price' => $model->price,
            'price' => $model->price,
            'notes' => $model->notes,
            'discount' => $model->discount,
            'total-sizes' => (int)$num_of_mod,
            'active' => Item::where('id',$model->item->id)->where('enabled',1)->count() > 0
        ];

        return $this->transformAll($model, $relationships, $data);

    }

    public function includeModifiers(ItemOrder $model)
    {
        $modifiers = $model->itemOrderModifiers;

        return $this->collection($modifiers, new OrderItemModifierTransformer(),'modifier');
    }

    public function includeIngredients(ItemOrder $model)
    {
        $ingredients = $model->itemOrderIngredients;

        return $this->collection($ingredients, new OrderItemIngredientTransformer(),'item-ingredient');
    }



}