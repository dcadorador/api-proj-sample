<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Ingredient;
use App\Api\V1\Models\ItemIngredient;
use App\Api\V1\Models\Item;

use App\Api\V1\Transformers\ItemIngredientTransformer;


class ItemIngredientController extends ApiController
{

    public function show(Request $request, $menu, $category, $item, $itemIngredient)
    {
        $itemIngredient = ItemIngredient::find($itemIngredient);

        if ($itemIngredient === null) {
            return response()->json(['error' => [
                $this->responseArray(1040,404)
            ]], 404);
        }

        return $this->response->item($itemIngredient, new ItemIngredientTransformer, ['key' => 'item-ingredient']);
    }

    public function index(Request $request, $item) 
    {
        $item = Item::find($item);

        if ($item === null) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        $ingredients = $item->ingredients();
        /*$ingredients = $ingredients->whereHas('ingredient', function($query){
            $query->where('enabled',1);
        })->paginate($this->perPage);*/
        $ingredients = $this->filterByEnabled($request,$ingredients);
        $ingredients->orderBy('display_order','asc');

        return $this->response->paginator($ingredients->paginate($this->perPage), new ItemIngredientTransformer, ['key' => 'item-ingredient']);
    }

    public function store(Request $request, $menu, $category, $item)
    {
        $this->getConcept($request,true);

        $item = Item::findOrFail($item);
        $ingredient = Ingredient::findOrFail($request->input('ingredient'));

        $itemIngredient = new ItemIngredient();
        $itemIngredient->item_id = $item->id;
        $itemIngredient->ingredient_id = $request->input('ingredient');

        $itemIngredient->quantity = $request->input('quantity', 1);
        $itemIngredient->maximum_quantity = $request->input('maximum-quantity', 1);
        $itemIngredient->minimum_quantity = $request->input('minimum-quantity', 0);
        $itemIngredient->enabled = $request->input('enabled', 1);
        //TODO: How to automate the sort order?
        $itemIngredient->display_order = $request->input('display-order', $ingredient->display_order);
        $itemIngredient->save();
        
        return $this->response->item($itemIngredient, new ItemIngredientTransformer(), ['key' => 'item-ingredient'])->setStatusCode(201);
    }

    public function edit(Request $request, $menu, $category, $item, $itemIngredient) {

        $this->getConcept($request,true);

        $itemIngredient = ItemIngredient::find($itemIngredient);
        if ($request->has('quantity')) {
            $itemIngredient->quantity = $request->input('quantity');
        }
        if ($request->has('maximum-quantity')) {
            $itemIngredient->maximum_quantity = $request->input('maximum-quantity');
        }
        if ($request->has('minimum-quantity')) {
            $itemIngredient->minimum_quantity = $request->input('minimum-quantity');
        }
        if ($request->has('display-order')) {
            $itemIngredient->display_order = $request->input('display-order');
        }
        if ($request->has('enabled')) {
            $itemIngredient->enabled = $request->input('enabled');
        }

        $itemIngredient->update();

        return $this->response->item($itemIngredient, new ItemIngredientTransformer(), ['key' => 'item-ingredient']);
    }

}