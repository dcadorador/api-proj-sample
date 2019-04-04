<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\ModifierGroup;
use App\Api\V1\Models\ItemModifierGroup;
use App\Api\V1\Models\Item;


use App\Api\V1\Transformers\ModifierGroupTransformer;
use App\Api\V1\Transformers\ItemModifierGroupTransformer;



class ModifierGroupController extends ApiController
{

    public function show($modifierGroup)
    {
        $modifierGroup = ModifierGroup::find($modifierGroup);

        if($modifierGroup === null) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        return $this->response->item($modifierGroup, new ModifierGroupTransformer, ['key' => 'modifier-groups']);
    }

    public function showItemModGrp(Request $request, $item,$modifierGroup)
    {
        $itemModifierGroup = ItemModifierGroup::where('item_id',$item)
            ->where('modifier_group_id',$modifierGroup)
            ->first();


        if($modifierGroup === null) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        return $this->response->item($itemModifierGroup, new ItemModifierGroupTransformer, ['key' => 'modifier-group']);
    }

    public function index(Request $request, $item = null) 
    {
        if ($item) {
            $item = Item::find($item);

            if ($item === null) {
                return response()->json(['error' => [
                    $this->responseArray(1015, 404)
                ]], 404);
            }
            //$modifierGroups = $item->modifierGroups();

            $itemModifierGroup = ItemModifierGroup::where('item_id',$item->id);
            $itemModifierGroup = $this->filterByEnabled($request,$itemModifierGroup);
            $itemModifierGroup->orderBy('display_order','asc');

            return $this->response->paginator($itemModifierGroup->paginate($this->perPage), new ItemModifierGroupTransformer, ['key' => 'modifier-groups']);
        }
        else {
            $modifierGroups = ModifierGroup::where('concept_id', $this->getConcept($request)->id);
        }

        $modifierGroups = $this->filterByEnabled($request, $modifierGroups);
        $modifierGroups->orderBy('enabled', 'desc')->orderBy('display_order', 'asc');

        return $this->response->paginator($modifierGroups->paginate($this->perPage), new ModifierGroupTransformer, ['key' => 'modifier-groups']);
    }

    public function store(Request $request, $menus = null, $categories = null, $item = null)
    {
        $this->getConcept($request,true);

        if ($item && $request->has('modifier-group')) {
            // Add this existing modifier group to the item
            $modifierGroupId = $request->input('modifier-group');

            // Check if it already exists first.
            $item = Item::find($item);

            if ($item === null) {
                return response()->json(['error' => [
                    $this->responseArray(1015, 404)
                ]], 404);
            }

            $modifierGroup = ModifierGroup::find($modifierGroupId);

            if($modifierGroup === null) {
                return response()->json(['error' => [
                    $this->responseArray(1017,404)
                ]], 404);
            }

            // check if item modifier group exists by using the id and modifier group id
            $itemModifierGroup = ItemModifierGroup::where('item_id',$item->id)
                ->where('modifier_group_id',$modifierGroupId)
                ->first();

            /*foreach ($item->modifierGroups as $mg) {
                if ($mg->id == $modifierGroupId) {
                    // Already exists, just return
                    return $this->response->noContent();
                }
            }*/
            $min = $request->input('minimum', null);
            $max = $request->input('maximum', null);
            $display_order = $request->input('display-order',0);

            $code = $request->input('code',null);
            if($code) {
                $modifierGroup->code = $code;
                $modifierGroup->update();
            }

            if(!$itemModifierGroup) {
                $itemModifierGroup = new ItemModifierGroup();
                $itemModifierGroup->item_id = $item->id;
                $itemModifierGroup->modifier_group_id = $modifierGroupId;
                $itemModifierGroup->display_order = $display_order;
                $itemModifierGroup->minimum =  $min ? $min : $modifierGroup->minimum;
                $itemModifierGroup->maximum =  $max ? $max : $modifierGroup->$max;
                $itemModifierGroup->enabled = $request->input('enabled', 1);
                $itemModifierGroup->save();
            } else {
                $itemModifierGroup->display_order = $display_order;
                $itemModifierGroup->minimum = $min ? $min : $modifierGroup->minimum;
                $itemModifierGroup->minimum = $max ? $max : $modifierGroup->$max;
                $itemModifierGroup->enabled = $request->input('enabled',1);
                $itemModifierGroup->update();
            }

            return $this->response->item($itemModifierGroup, new ItemModifierGroupTransformer, ['key' => 'modifier-group']);
        }


        $modifierGroup = new ModifierGroup();

        //TODO: How to automate the sort order?
        $modifierGroup->concept_id = $this->getConcept($request)->id;
        $modifierGroup->display_order = $request->input('display-order', 0);
        $modifierGroup->name = $this->getLocalizedInput($request, 'name');
        //$modifierGroup->image_uri = $this->saveUploadedFile($request);
        $modifierGroup->minimum = $request->input('minimum', 0);
        $modifierGroup->maximum = $request->input('maximum', 5);
        $modifierGroup->enabled = $request->input('enabled', true);
        $modifierGroup->code = $request->input('code');
        $modifierGroup->save();
        
        return $this->response->item($modifierGroup, new ModifierGroupTransformer, ['key' => 'modifier-group'])->setStatusCode(201);
    }

    public function edit(Request $request, $modifierGroup)
    {
        $this->getConcept($request,true);

        $modifierGroup = ModifierGroup::where('id',$modifierGroup)
            ->first();

        if (!$modifierGroup) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        if ($request->has('display-order')) {
            $modifierGroup->display_order = $request->input('display-order');
        }

        if ($request->has('name')) {
            $modifierGroup->name = $this->getLocalizedInput($request, 'name');
        }

        if ($request->hasFile('image')) {
            $modifierGroup->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            if ($request->has('image-uri')) {
                $modifierGroup->image_uri = $request->input('image-uri');
            }
        }

        if ($request->has('minimum')) {
            $modifierGroup->minimum = $request->input('minimum');
        }
        if ($request->has('maximum')) {
            $modifierGroup->maximum = $request->input('maximum');
        }
        if ($request->has('enabled')) {
            $modifierGroup->enabled = $request->input('enabled');
        }
        if ($request->has('code')) {
            $modifierGroup->code = $request->input('code');
        }
        $modifierGroup->update();

        return $this->response->item($modifierGroup, new ModifierGroupTransformer, ['key' => 'modifier-group'])->setStatusCode(201);
    }

    public function editItemModGrp (Request $request, $menu, $category, $item, $modifierGroup)
    {
        $this->getConcept($request,true);

        $itemModifierGroup = ItemModifierGroup::where('item_id',$item)
            ->where('modifier_group_id',$modifierGroup)
            ->first();

        if (!$itemModifierGroup) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        $modGrp = $itemModifierGroup->modifierGroup;

        if ($request->has('minimum')) {
            $itemModifierGroup->minimum = $request->input('minimum');
        }
        if ($request->has('maximum')) {
            $itemModifierGroup->maximum = $request->input('maximum');
        }
        if ($request->has('enabled')) {
            $itemModifierGroup->enabled = $request->input('enabled');
        }
        if ($request->has('display-order')) {
            $itemModifierGroup->display_order = $request->input('display-order');
        }
        if ($request->has('code')) {
            if($modGrp) {
                $modGrp->code = $request->input('code');
                $modGrp->update();
            }
        }
        if ($request->has('name')) {
            if($modGrp) {
                $modGrp->name = $this->getLocalizedInput($request, 'name');
                $modGrp->update();
            }
        }
        if ($request->hasFile('image')) {
            if($modGrp) {
                $modGrp->image_uri = $this->saveUploadedFile($request, 'image');
                $modGrp->update();
            }
        }
        else {
            if ($request->has('image-uri')) {
                if($modGrp) {
                    $modGrp->image_uri = $request->input('image-uri');
                    $modGrp->update();
                }
            }
        }

        $itemModifierGroup->update();

        return $this->response->item($itemModifierGroup, new ItemModifierGroupTransformer, ['key' => 'modifier-group']);
    }
}