<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Item;
use App\Api\V1\Transformers\ItemTransformer;
use Illuminate\Http\Request;

use App\Api\V1\Models\Category;
use App\Api\V1\Models\Menu;

use App\Api\V1\Transformers\CategoryTransformer;


class CategoryController extends ApiController
{

    public function show(Request $request, $category)
    {
        $cacheKey = count($request->all()) > 0 ? md5($category.'-'.json_encode($request->all()).'-'.$this->getLocale($request)) :  md5($category.'-'.$this->getLocale($request));

        if(app('cache')->store('redis')->has($cacheKey)) {
            return response()->json(app('cache')->store('redis')->get($cacheKey));
        }

        $category = Category::where('id',$category)->first();

        if(!$category) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        $data =  $this->response->item($category, new CategoryTransformer, ['key' => 'category'])->morph()->getContent();
        app('cache')->store('redis')->put($cacheKey,$data,1200);
        return response()->json($data);
    }

    public function index(Request $request, $menu) 
    {
        $cacheKey =  count($request->all()) > 0 ? md5('category-'.$menu.'-'.json_encode($request->all()).'-'.$this->getLocale($request)) : md5('category-'.$menu.'-'.$this->getLocale($request));

        if(app('cache')->store('redis')->has($cacheKey)) {
            return response()->json(app('cache')->store('redis')->get($cacheKey));
        }

        $concept = $this->getConcept($request);
        $menu = Menu::where('id',$menu)
                ->where('concept_id',$concept->id)
                ->first();

        if ($menu === null) {
            return response()->json(['error' => [
                $this->responseArray(1018,404)
            ]], 404);
        }

        $categories = $this->filterByEnabled($request, $menu->categories());

        $categories->orderBy('enabled', 'desc')->orderBy('display_order','asc');

        $data =  $this->response->paginator($categories->paginate($this->perPage), new CategoryTransformer, ['key' => 'category'])->morph()->getContent();
        app('cache')->store('redis')->put($cacheKey,$data,1200);
        return response()->json($data);
    }

    public function store(Request $request, $menu)
    {
        $concept = $this->getConcept($request,true);
        $menu = Menu::where('id',$menu)
            ->where('concept_id',$concept->id)
            ->first();

        if ($menu === null) {
            return response()->json(['error' => [
                $this->responseArray(1018,404)
            ]], 404);
        }

        $category = new Category();
        $category->menu_id = $menu->id;
        //TODO: How to automate the sort order?
        $category->code = $request->input('code');
        $category->display_order = $request->input('display-order', 0);
        $category->enabled = $request->input('enabled', true);
        $category->name = $this->getLocalizedInput($request, 'name');
        $category->description = $this->getLocalizedInput($request, 'description');

        if ($request->has('image')) {
            $category->image_uri = $this->saveFile($request, 'image');
        }
        else {
            $category->image_uri = $request->input('image-uri');
        }

        $category->save();
        $cacheKey = md5($category->id.'-'.$this->getLocale($request));
        app('cache')->store('redis')->flush();
        app('cache')->store('redis')->put($cacheKey,$this->response->item($category, new CategoryTransformer, ['key' => 'category'])->morph()->getContent(),1200);
        return $this->response->item($category, new CategoryTransformer, ['key' => 'category'])->setStatusCode(201);
    }

    public function edit(Request $request, $menu, $category)
    {
        $this->getConcept($request,true);
        $category = Category::find($category);

        if ($category === null) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        $cacheKey = md5($category->id.'-'.$this->getLocale($request));

        //TODO: How to automate the sort order?
        if ($request->has('display-order')) {
            $category->display_order = $request->input('display-order');
        }
        if ($request->has('name')) {
            $category->name = $this->getLocalizedInput($request, 'name');
        }
        if ($request->has('description')) {
            $category->description = $this->getLocalizedInput($request, 'description');
        }
        if ($request->hasFile('image')) {
            $category->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            if ($request->has('image-uri')) {
                $category->image_uri = $request->input('image-uri');
            }
        }
        if ($request->has('code')) {
            $category->code = $request->input('code');
        }
        if ($request->has('enabled')) {
            $category->enabled = $request->input('enabled');
        }

        $category->update();
        app('cache')->store('redis')->flush();
        app('cache')->store('redis')->put($cacheKey,$this->response->item($category, new CategoryTransformer, ['key' => 'category'])->morph()->getContent(),1200);
        return $this->response->item($category, new CategoryTransformer, ['key' => 'category']);
    }

    public function getRecommendedItems(Request $request, $menu, $category)
    {
        $concept = $this->getConcept($request);

        $menu = Menu::where('id',$menu)
            ->where('concept_id',$concept->id)
            ->first();

        if (!$menu) {
            return response()->json(['error' => [
                $this->responseArray(1018,404)
            ]], 404);
        }

        $items = Item::where('category_id',$category)
            ->orderByRaw('RAND()')
            ->take(5)
            ->get();

        return $this->response->collection($items, new ItemTransformer, ['key' => 'item']);
    }
}