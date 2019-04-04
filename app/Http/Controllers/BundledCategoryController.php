<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\BundledCategory;
use App\Api\V1\Models\Item;

use App\Api\V1\Transformers\BundledCategoryTransformer;


class BundledCategoryController extends ApiController
{

    public function show($bundledCategory)
    {
        $bundledCategory = BundledCategory::where('id',$bundledCategory)->first();

        if(!$bundledCategory){
            return response()->json(['error' => [
                $this->responseArray(1015,404)
            ]], 404);
        }

        return $this->response->item($bundledCategory, new BundledCategoryTransformer, ['key' => 'bundled-category']);
    }

    public function index(Request $request, $bundledItem) 
    {
        $bundledCategories = BundledCategory::where('bundled_item_id', $bundledItem);

        if ($bundledCategories === null) {
            return response()->json(['error' => [
                $this->responseArray(1015,404)
            ]], 404);
        }

        return $this->response->paginator($bundledCategories->paginate($this->perPage), new BundledCategoryTransformer, ['key' => 'bundled-category']);
    }

    public function store(Request $request, $category)
    {
    }

}