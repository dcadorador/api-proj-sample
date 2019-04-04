<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Ingredient;

use App\Api\V1\Transformers\IngredientTransformer;


class IngredientController extends ApiController
{

    public function show($ingredient)
    {
        $ingredient = Ingredient::find($ingredient);

        if ($ingredient === null) {
            return response()->json(['error' => [
                $this->responseArray(1040,404)
            ]], 404);
        }

        return $this->response->item($ingredient, new IngredientTransformer, ['key' => 'ingredient']);
    }

    public function index(Request $request) 
    {
        $concept = $this->getConcept($request);
        $data = $this->filterByEnabled($request, Ingredient::where('concept_id', $concept->id));
        $data->orderBy('display_order', 'asc');

        return $this->response->paginator($data->paginate($this->perPage), new IngredientTransformer, ['key' => 'ingredient']);
    }

    public function store(Request $request)
    {
        $concept = $this->getConcept($request,true);

        $ingredient = new Ingredient();
        //TODO: How to automate the sort order?
        $ingredient->display_order = $request->input('display-order', 0);
        $ingredient->name = $this->getLocalizedInput($request, 'name');
        $ingredient->price = $request->input('price', 0);
        $ingredient->code = $request->input('code', '');
        $ingredient->enabled = $request->input('enabled', true);
        $ingredient->concept_id = $concept->id;


        if ($request->has('image')) {
            $ingredient->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            $ingredient->image_uri = $request->input('image-uri');
        }

        $ingredient->save();
        
        return $this->response->item($ingredient, new IngredientTransformer(), ['key' => 'ingredient'])->setStatusCode(201);
    }

    public function edit(Request $request, $ingredient)
    {
        $this->getConcept($request,true);

        $ingredient = Ingredient::find($ingredient);

        if ($ingredient === null) {
            return response()->json(['error' => [
                $this->responseArray(1040,404)
            ]], 404);
        }

        //TODO: How to automate the sort order?
        if ($request->has('display-order')) {
            $ingredient->display_order = $request->input('display-order');
        }
        if ($request->has('name')) {
            $ingredient->name = $this->getLocalizedInput($request, 'name');
        }
        if ($request->hasFile('image')) {
            $ingredient->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            if ($request->has('image-uri')) {
                $ingredient->image_uri = $request->input('image-uri');
            }
        }
        if ($request->has('code')) {
            $ingredient->code = $request->input('code');
        }
        if ($request->has('enabled')) {
            $ingredient->enabled = $request->input('enabled');
        }
        if ($request->has('price')) {
            $ingredient->price = $request->input('price');
        }
        if ($request->has('calorie-count')) {
            $ingredient->calorie_count = $request->input('calorie-count');
        }

        $ingredient->update();
        
        return $this->response->item($ingredient, new IngredientTransformer, ['key' => 'ingredient']);
    }

}