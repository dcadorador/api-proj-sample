<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Menu;
use Illuminate\Http\Request;

use App\Api\V1\Models\Category;
use App\Api\V1\Models\Item;

use App\Api\V1\Transformers\ItemTransformer;
use App\Api\V1\Transformers\TimedEventTransformer;
use App\Api\V1\Transformers\LocationTransformer;


class ItemController extends ApiController
{

    public function show(Request $request, $item)
    {
        $cacheKey = count($request->all()) > 0 ? md5($item.'-'.json_encode($request->all()).'-'.$this->getLocale($request)) :  md5($item.'-'.$this->getLocale($request));

        if(app('cache')->store('redis')->has($cacheKey)) {
            return response()->json(app('cache')->store('redis')->get($cacheKey));
        }

        $item = Item::find($item);

        if ($item === null) {
           return response()->json(['error' => [
              $this->responseArray(1038,404)
           ]], 404);
        }

        $data = $this->response->item($item, new ItemTransformer, ['key' => 'item'])->morph()->getContent();
        app('cache')->store('redis')->put($cacheKey,$data,1200);
        return response()->json($data);
    }

    public function index(Request $request, $category)
    {
        $cacheKey = count($request->all()) > 0 ? md5('item-'.$category.'-'.json_encode($request->all()).'-'.$this->getLocale($request)) : md5('item-'.$category.'-'.$this->getLocale($request));

        if(app('cache')->store('redis')->has($cacheKey)) {
            return response()->json(app('cache')->store('redis')->get($cacheKey));
        }

        $category = Category::find($category);

        if ($category === null) {
            return response()->json(['error' => [
               $this->responseArray(1017,404)
            ]], 404);
        }

        $items = $this->filterByEnabled($request, $category->items());


        $sort = $request->input('sort', []);

        $locale = $this->getLocale($request);

        if(array_key_exists('price', $sort)) {
            $items = $items->orderBy('price', $sort['price']);
        } else if (array_key_exists('name', $sort)) {
            $items = $items->leftJoin('translations', 'translations.group_id' , '=', 'items.name')
                ->where('locale', $locale)->orderBy('translations.value', $sort['name']);
        } else {
            $items = $items->orderBy('enabled', 'desc')->orderBy('display_order', 'asc');
        }

        $data = $this->response->paginator($items->paginate($this->perPage), new ItemTransformer, ['key' => 'item'])->morph()->getContent();
        app('cache')->store('redis')->put($cacheKey,$data,1200);
        return response()->json($data);
    }

    public function store(Request $request, $category)
    {
        $this->getConcept($request,true);

        $category = Category::find($category);

        if ($category === null) {
            return response()->json(['error' => [
                $this->responseArray(1017,404)
            ]], 404);
        }

        $item = new Item();
        $item->category_id = $category->id;
        $item->code = $request->input('code');
        //TODO: How to automate the sort order?
        $display_order = $request->input('display-order', 0);
        $item->display_order = is_null($display_order) ? 0 : $display_order;
        $item->name = $this->getLocalizedInput($request, 'name');
        $item->description = $this->getLocalizedInput($request, 'description');
        $price = $request->input('price',0);
        $item->price = (is_null($price) || $price == '') ? 0 : $price;
        $item->enabled = $request->input('enabled', true);

        if ($request->has('image')) {
            $item->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            $item->image_uri = $request->input('image-uri');
        }

        $item->save();
        $cacheKey = md5($item->id.'-'.$this->getLocale($request));
        app('cache')->store('redis')->flush();
        app('cache')->store('redis')->put($cacheKey,$this->response->item($item, new ItemTransformer, ['key' => 'item'])->morph()->getContent(),1200);
        return $this->response->item($item, new ItemTransformer, ['key' => 'item'])->setStatusCode(201);
    }

    public function edit(Request $request, $item)
    {
        $this->getConcept($request,true);

        $item = Item::find($item);

        if ($item === null) {
            return response()->json(['error' => [
                $this->responseArray(1038, 404)
            ]], 404);
        }

        // ADDED FOR THE CACHING OF ITEMS
        $cacheKey = md5($item->id.'-'.$this->getLocale($request));

        //TODO: How to automate the sort order?
        if ($request->has('display-order')) {
            $item->display_order = $request->input('display-order');
        }
        if ($request->has('name')) {
            $item->name = $this->getLocalizedInput($request, 'name');
        }
        if ($request->has('description')) {
            $item->description = $this->getLocalizedInput($request, 'description');
        }

        if ($request->hasFile('image')) {
            $item->image_uri = $this->saveUploadedFile($request, 'image');
        } else {
            if ($request->has('image-uri')) {
                $item->image_uri = $request->input('image-uri');
            }
        }

        if ($request->has('code')) {
            $item->code = $request->input('code');
        }
        if ($request->has('enabled')) {
            $item->enabled = $request->input('enabled');
        }
        // added to update the price
        if ($request->has('price')) {
            $item->price = $request->input('price');
        }

        if ($request->has('calorie-count')) {
            $item->calorie_count = $request->input('calorie-count');
        }

        $item->update();
        app('cache')->store('redis')->flush();
        app('cache')->store('redis')->put($cacheKey,$this->response->item($item, new ItemTransformer, ['key' => 'item'])->morph()->getContent(),1200);
        return $this->response->item($item, new ItemTransformer, ['key' => 'item']);
    }

    public function getAllItems(Request $request, $menu)
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

        // gather all category id for the menu
        $categories = $menu->categories()->pluck('id')->all();

        // check if there is a code filter
        $filter = $request->input('filter',[]);

        $sort = $request->input('sort', []);

        // filter based on the item code
        if(array_key_exists('code', $filter)) {
            $items = Item::whereIn('category_id',$categories)
                ->where('code',$filter['code']);
        } elseif(array_key_exists('id', $filter)) {
            $ids = explode(',', $filter['id']);
            $items = Item::whereIn('category_id',$categories)
                ->whereIn('id', $ids);
        } else {
            $items = Item::whereIn('category_id',$categories);
        }

        $locale = $this->getLocale($request);

        if(array_key_exists('price', $sort)) {
            $items = $items->orderBy('price', $sort['price']);
        } else if (array_key_exists('name', $sort)) {
            $items = $items->leftJoin('translations', 'translations.group_id' , '=', 'items.name')
                ->where('locale', $locale)->orderBy('translations.value', $sort['name']);
        }

        $items = $items->paginate($this->perPage);

        return $this->response->paginator($items, new ItemTransformer, ['key' => 'item']);
    }

    public function getPopularItems(Request $request, $menu)
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

        $categories = $menu->categories()->pluck('id')->all();

        $items = Item::whereIn('category_id',$categories)
            ->orderByRaw('RAND()')
            ->take(15)
            ->paginate(15);

        return $this->response->paginator($items, new ItemTransformer, ['key' => 'item']);
    }

    public function getRecommendedItems(Request $request, $menu)
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

        $categories = $menu->categories()->pluck('id')->all();

        $items = Item::whereIn('category_id',$categories)
            ->orderByRaw('RAND()')
            ->take(15)
            ->paginate(15);

        return $this->response->paginator($items, new ItemTransformer, ['key' => 'item']);
    }

    public function getTimedEvents(Request $request, $item)
    {
        $concept = $this->getConcept($request);
        $item = Item::find($item);

        if ($item === null) {
            return response()->json(['error' => [
                $this->responseArray(1038,404)
            ]], 404);
        }

        $timed_events = $item->timedEvents()->where('concept_id',$concept->id)->orderBy('created_at','DESC');

        $data = $timed_events->paginate($this->perPage)->appends(app('request')->except('page'));
        return $this->response->paginator($data, new TimedEventTransformer, ['key' => 'timed-event']);
    }

    public function getItemLocation(Request $request, $item)
    {
        $concept = $this->getConcept($request);
        $item = Item::find($item);

        if ($item === null) {
            return response()->json(['error' => [
                $this->responseArray(1038,404)
            ]], 404);
        }

        $data = $item->locations()->paginate($this->perPage);
        return $this->response->paginator($data, new LocationTransformer, ['key' => 'location']);
    }

}