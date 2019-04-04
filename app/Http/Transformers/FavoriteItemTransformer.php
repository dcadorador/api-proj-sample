<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Favorite;
use App\Api\V1\Models\Modifier;
use App\Api\V1\Services\HamburginiMealService;

class FavoriteItemTransformer extends ApiTransformer
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'modifiers',
        'ingredients'
    ];

    public function transform(Favorite $model)
    {

        $relationships = [
            'item' => $model->item->uri
        ];

        $service = new HamburginiMealService();
        $new_price = $service->getCorrectPrice($model);
        $num_of_mod = $service->getNumberOfSizeMods($model);

        return $this->transformAll($model, $relationships, [
                //'item-id' => $model->item->id,
                'name' => $model->item->name,
                'image-uri' => $model->item->image_uri,
                'price' => $new_price ==  0 ? $model->item->price : $new_price,
                'total-sizes' => (int)$num_of_mod,
                'modifiers' => $this->modifiers($model),
                'ingredients' => $this->ingredients($model)
        ]);

    }

    private function modifiers(Favorite $model)
    {
        $modifiers = $model->modifiers->all();
        $transform = new FavoriteModifierTransformer();
        return $transform->transformCollection($modifiers);
    }

    private function ingredients(Favorite $model)
    {
        $ingredients = $model->ingredients->all();
        $transform = new FavoriteIngredientTransformer();
        return $transform->transformCollection($ingredients);
    }

    /*private function modifiers(Favorite $model)
    {
        if(!$model->modifiers){
            return null;
        }

        $modifiers = $model->modifiers;

        $data = [];

        foreach ($modifiers as $modifier)
        {
            $info = new \stdClass();
            $mods = Modifier::where('id',$modifier->modifier_id)->first();
            if ($mods) {
                $info->id = $mods->id;
                $info->name = $mods->name;
                $info->quantity = $modifier->quantity;
                $info->links = new \stdClass();
                $info->links->self = $mods->uri;
                $data[] = $info;
            }
        }

        return $data;
    }*/

    /**
     * Include Modifiers
     *
     * @return League\Fractal\ItemResource
     */
    public function includeModifiers(Favorite $model)
    {
        $modifiers = $model->modifiers;

        return $this->collection($modifiers, new FavoriteModifierTransformer, 'modifier');
    }

    /**
     * Include Ingredients
     *
     * @return League\Fractal\ItemResource
     */
    public function includeIngredients(Favorite $model)
    {
        $ingredients = $model->ingredients;

        return $this->collection($ingredients, new FavoriteIngredientTransformer, 'item-ingredient');
    }
}