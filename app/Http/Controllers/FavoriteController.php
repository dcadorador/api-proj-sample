<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Customer;
use Illuminate\Http\Request;
use App\Api\V1\Controllers;
use App\Api\V1\Models\Favorite;
use App\Api\V1\Models\Item;
use App\Api\V1\Transformers\FavoriteItemTransformer;
use App\Api\V1\Models\FavoriteItemModifier;
use App\Api\V1\Models\FavoriteItemIngredient;
use App\Api\V1\Services\HamburginiMealService;
use App\Api\V1\Models\Modifier;

class FavoriteController extends ApiController
{
    public function index(Request $request, $customer)
    {
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
        $favorites = Favorite::whereHas('item', function($item) {
            $item->whereNull('deleted_at');
            })->where('customer_id',$customer)
            ->orderBy('created_at','DESC')
            ->paginate($this->perPage);

        return $this->response->paginator($favorites, new FavoriteItemTransformer(), ['key' => 'favorite-item']);
    }

    public function store(Request $request, $customer)
    {
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
        $favorite = new Favorite();
        $favorite->item_id = $request->json('item-id');
        $favorite->customer_id = $customer;
        $favorite->save();

        $item = Item::where('id',$favorite->item_id)->first();
        $category = $item->category()->first();

        if(!$item){
            return response()->json(['error' => [
                $this->responseArray(1038,404)
            ]], 404);
        }

        app('log')->info('FAVORITES - REQUEST FROM THE APP:'.json_encode($request->all()));

        $modifiers = $request->json('modifiers',null);
        $ingredients = $request->json('ingredients',null);
        $quantity = $request->json('quantity');

        $price = (int)$request->json('price');

        if($modifiers and count($modifiers) > 0) {
            foreach($modifiers as $modifier)
            {
                $modifier = (object)$modifier;
                //determine the modifier group name
                $mods = Modifier::find($modifier->id);
                $modGrp = $mods->modifierGroup()->first();
                
                $favorite_modifier = new FavoriteItemModifier();
                $favorite_modifier->favorite_item_id = $favorite->id;
                $favorite_modifier->modifier_id = $modifier->id;
                $favorite_modifier->quantity = $modifier->quantity;
                $favorite_modifier->price = $mods ? $mods->price : $modifier->price;
                $favorite_modifier->save();

                if(stripos(strtolower($modGrp->code),'size') !== false and strtolower($category->translate('en-us')->name) != 'meals' ) {
                    $price = (int)$modifier->price;
                } elseif(!stripos(strtolower($modGrp->code),'size') !== false and strtolower($category->translate('en-us')->name) != 'meals') {
                    $price += (int)$modifier->price * $modifier->quantity;
                }
            }
        }

        // moved here to make it available
        // added the correct item pricing for meals
        if($category and strtolower($category->translate('en-us')->name) == 'meals' and ($this->getConcept($request)->id == 1)) {
            $service = new HamburginiMealService();
            $items = $request->all();
            $items['id'] = $items['item-id'];
            unset($items['item-id']);
            $new_price = $service->calculateMealPrice(json_encode($items));
            //$price = $new_price * $quantity;
            $price = $new_price < $price ? $price : $new_price;
        }

        $favorite->price = $price;
        $favorite->save();

        if($ingredients && count($ingredients) > 0) {
            foreach($ingredients as $ingredient)
            {
                $ingredient = (object)$ingredient;
                $favorite_ingredient = new FavoriteItemIngredient();
                $favorite_ingredient->favorite_item_id = $favorite->id;
                // added the item_ingredient_id
                $favorite_ingredient->item_ingredients_id = $ingredient->id;
                // commented out to remove the ingredient id relation
                //$favorite_ingredient->ingredient_id = $ingredient->id;
                $favorite_ingredient->quantity = $ingredient->quantity;
                $favorite_ingredient->save();
            }
        }

        return $this->response->item($favorite, new FavoriteItemTransformer(), ['key' => 'favorite-item']);
    }

    public function deleteItem(Request $request, $customer)
    {
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();

        $filter = $request->input('filter',[]);
        if (!array_key_exists('item',$filter)) {
                return $this->response->noContent();
        }

        $itemId = $filter['item'];
        $cust = Customer::with(['favorites'])
            ->where('id',$customer)
            ->first();

        $favorites = $cust->favorites;
        foreach($favorites as $favorite) {
            if ($favorite->item_id == $itemId) {
                $favorite->delete();
            }
        }        
        return $this->response->noContent();
    }

    public function delete(Request $request, $customer, $favorite)
    {
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
        $cust = Customer::with(['favorites'])
            ->where('id',$customer)
            ->first();

        $related = $request->input('_delete-related',null);

        $favorite = Favorite::where('id',$favorite)->first();

        if(!$favorite) {
            return response()->json(['error' => [
                $this->responseArray(1039,404)
            ]], 404);
        }

        $item_id = $favorite->item_id;

        $favorite->delete();

        if($related) {
            $favorites = Favorite::where('customer_id',$customer)
                ->where('item_id',$item_id)
                ->get();
            if($favorites){
                foreach($favorites as $favorite){
                    $favorite->delete();
                }
            }
        }

        return $this->response->noContent();
    }
}