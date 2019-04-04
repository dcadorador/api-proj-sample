<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\BundledItem;
use App\Api\V1\Models\BundledCategory;
use App\Api\V1\Models\Item;

use App\Api\V1\Transformers\BundledItemTransformer;


class BundledItemController extends ApiController
{

    public function show($bundledItem)
    {
        $bundledItem = BundledItem::where('id',$bundledItem)->first();

        if(!$bundledItem){
            return response()->json(['error' => [
                $this->responseArray(1016,404)
            ]], 404);
        }

        return $this->response->item($bundledItem, new BundledItemTransformer, ['key' => 'bundled-item']);
    }

    public function index(Request $request, $item) 
    {
        $bundledItems = BundledItem::where('parent_item_id', $item);

        if ($bundledItems === null) {
            return response()->json(['error' => [
                $this->responseArray(1016,404)
            ]], 404);
        }

        return $this->response->paginator($bundledItems->paginate($this->perPage), new BundledItemTransformer, ['key' => 'bundled-item']);
    }

    public function store(Request $request, $item)
    {
        $primaryItemId = $request->input('primary-item-id');
        $bundledCategories = $request->json('categories');

        $bundledItem = new BundledItem();
        $bundledItem->parent_item_id = $item;
        $bundledItem->primary_item_id = $primaryItemId;
        $bundledItem->save();

        foreach ($bundledCategories as $bc) {
            $bundledCategory = new BundledCategory();
            $bundledCategory->bundled_item_id = $bundledItem->id;
            $bundledCategory->category_id = $bc['id'];
            $bundledCategory->default_item_id = $bc['default-item-id'];
            $bundledCategory->save();
        }
    }

}