<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\ModifierGroup;
use App\Api\V1\Models\Modifier;

use App\Api\V1\Transformers\ModifierTransformer;


class ModifierController extends ApiController
{

    public function show($modifier)
    {
        $modifier = Modifier::findOrFail($modifier);

        return $this->response->item($modifier, new ModifierTransformer, ['key' => 'modifier']);
    }

    public function index(Request $request, $modifierGroup) 
    {
        $modifierGroup = ModifierGroup::findOrFail($modifierGroup);

        $modifiers = $this->filterByEnabled($request, $modifierGroup->modifiers());
        $modifiers->orderBy('enabled', 'desc')->orderBy('display_order', 'asc');

        return $this->response->paginator($modifiers->paginate($this->perPage), new ModifierTransformer, ['key' => 'modifier']);
    }

    public function store(Request $request, $modifierGroup)
    {
        $this->getConcept($request,true);

        $modifierGroup = ModifierGroup::findOrFail($modifierGroup);

        $modifier = new Modifier();
        $modifier->modifier_group_id = $modifierGroup->id;
        //TODO: How to automate the sort order?
        $modifier->display_order = $request->input('display-order', 0);
        $modifier->name = $this->getLocalizedInput($request, 'name');
        $modifier->price = $request->input('price', 0);
        $modifier->minimum = $request->input('minimum',0);
        $modifier->maximum = $request->input('maximum',3);
        $modifier->enabled = $request->input('enabled', true);

        if ($request->has('image')) {
            $modifier->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            $modifier->image_uri = $request->input('image-uri');
        }
 
        $modifier->save();
        
        return $this->response->item($modifier, new ModifierTransformer, ['key' => 'modifier'])->setStatusCode(201);
    }

    public function edit(Request $request, $modifier)
    {
        $this->getConcept($request,true);

        $modifier = Modifier::find($modifier);

        if ($modifier === null) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        //TODO: How to automate the sort order?
        if ($request->has('display-order')) {
            $modifier->display_order = $request->input('display-order');
        }
        if ($request->has('name')) {
            $modifier->name = $this->getLocalizedInput($request, 'name');
        }
        if ($request->hasFile('image')) {
            $modifier->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            if ($request->has('image-uri')) {
                $modifier->image_uri = $request->input('image-uri');
            }
        }
        if ($request->has('price')) {
            $modifier->price = $request->input('price');
        }
        if ($request->has('minimum')) {
            $modifier->minimum = $request->input('minimum');
        }
        if ($request->has('maximum')) {
            $modifier->maximum = $request->input('maximum');
        }
        if ($request->has('enabled')) {
            $modifier->enabled = $request->input('enabled');
        }
        if ($request->has('calorie-count')) {
            $modifier->calorie_count = $request->input('calorie-count');
        }
        $modifier->update();
        
        return $this->response->item($modifier, new ModifierTransformer, ['key' => 'modifier']);
    }

}