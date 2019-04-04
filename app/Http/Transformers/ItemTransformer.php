<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Item;
use App\Api\V1\Helpers\AuthHelper;
use App\Api\V1\Models\Favorite;
use Carbon\Carbon;

class ItemTransformer extends ApiTransformer
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'ingredients',
        'modifierGroups',
        'locations'

    ];

    public function transform(Item $model)
    {

        $relationships = [
            'modifier-groups' => $model->uri.'/modifier-groups',
            'item-ingredients' => $model->uri.'/item-ingredients',
            'locations' => $model->uri.'/locations'
        ];

        if ($model->bundledItems) {
            $relationships = array_add($relationships, 'bundled-items', $model->uri.'/bundled-items');
        }

        $data = [
            'code' => $model->code,
            'display-order' => $model->display_order,
            'name' => $model->name,
            'description' => $model->description,
            'image-uri' => $model->image_uri,
            'price' => (int)$model->price,
            'calorie-count' => $model->calorie_count,
            'favorite' => false,
            'enabled' => $model->enabled
        ];

        if ($model->timedEvents()->count() > 0) {
            $relationships = array_add($relationships, 'timed-events', $model->uri.'/timed-events');
            $time_event = $model->timedEvents()->where('from_date','<=',Carbon::now()->setTimezone('GMT+3')->format('Y-m-d h:i:s'))
                    ->where('to_date','>=',Carbon::now()->setTimezone('GMT+3')->format('Y-m-d h:i:s'));
            if($time_event->count() > 0) {
                $data['has-timed-event'] = true;
            }
        }

        if ($subscriber = AuthHelper::getAuthenticatedUser()) {
            $customer = $subscriber->userable()->first();
            $data['favorite'] = Favorite::where('item_id',$model->id)->where('customer_id',$customer->id)->count() > 0;
        }

        return $this->transformAll($model, $relationships, $data);
	}

    /**
     * Include Ingredients
     *
     * @return League\Fractal\ItemResource
     */
    public function includeIngredients(Item $model)
    {
        $ingredients = $model->ingredients;

        return $this->collection($ingredients, new ItemIngredientTransformer, 'ingredient');
    }

    /**
     * Include Modifier Groups
     *
     * @return League\Fractal\ItemResource
     */
    public function includeModifierGroups(Item $model)
    {
        $modifierGroups = $model->modifierGroups;

        return $this->collection($modifierGroups, new ModifierGroupTransformer, 'modifier-group');
    }

    public function includeLocations(Item $model)
    {
        $locations = $model->locations;

        return $this->collection($locations, new LocationTransformer, 'location');
    }

}