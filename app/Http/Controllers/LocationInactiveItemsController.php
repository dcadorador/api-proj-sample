<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Item;
use App\Api\V1\Models\Modifier;
use App\Api\V1\Transformers\ItemTransformer;
use App\Api\V1\Transformers\ModifierTransformerB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Api\V1\Models\Location;
use App\Api\V1\Transformers\LocationTransformer;
use App\Api\V1\Transformers\LocationTransformerB;
use App\Api\V1\Transformers\DeviceTransformer;
use App\Api\V1\Transformers\DeliveryAreaTransformer;
use App\Api\V1\Transformers\LocationGeofenceTransformer;
use App\Api\V1\Models\DeliveryArea;
use App\Api\V1\Models\Geofence;
use App\Api\V1\Services\PointLocationService;
use App\Api\V1\Models\Point;
use App\Api\V1\Models\CustomerAddress;
use Carbon\Carbon;

class LocationInactiveItemsController extends ApiController
{
    public function index(Request $request, $locationId)
    {
        $limit = $request->input('limit') ? $request->input('limit') : $this->perPage;

        $location = Location::find($locationId);

        $locationItems = $location
            ->items()
            ->paginate($limit);

        if($request->input('filter')){
            $filter = $request->input('filter');

            if(array_key_exists('id', $filter)) {

                $ids = explode(',', $filter['id']);

                if(count($ids)) {
                    $locationItems = $location
                        ->items()
                        ->whereIn('item_id', $ids)
                        ->paginate($limit);
                }

                return $this->response->paginator($locationItems, new ItemTransformer, ['key' => 'items']);
            }

            return response()->json(['error' => [
                $this->responseArray(1038,400)
            ]], 400);
        }

        // return collection with paginator
        return $this->response->paginator($locationItems, new ItemTransformer, ['key' => 'items']);
    }

    public function store(Request $request, $locationId)
    {
        $this->getConcept($request,true);
        app('cache')->store('redis')->flush();
        app('cache')->flush();

        $limit = $request->input('limit') ? $request->input('limit') : $this->perPage;

        $location = Location::find($locationId);

        $locationItems = $location
            ->items()
            ->paginate($limit);

        $items = $request->input('items', []);

        $locationItemIds = $location->items()->pluck('item_id')->toArray();

        $items = collect($items)->reject(function($value) use($locationItemIds){
            return in_array($value, $locationItemIds);
        })->toArray();

        if(count($items)) {

            foreach ($items as $item) {
                $itemExist = Item::find($item);

                if(!$itemExist) {
                    continue;
                }

                $location->items()->attach($item,['created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString()]);
            }

            $locationItems = $location
                ->items()
                ->orderBy('created_at','DESC')
                ->paginate($limit);

            return $this->response->paginator($locationItems, new ItemTransformer, ['key' => 'items']);
        }

        return $this->response->paginator($locationItems, new ItemTransformer, ['key' => 'items']);
    }

    public function destroy(Request $request, $locationId, $inactiveItemId)
    {
        $this->getConcept($request,true);
        app('cache')->store('redis')->flush();
        app('cache')->flush();

        $limit = $request->input('limit') ? $request->input('limit') : $this->perPage;

        $location = Location::find($locationId);

        $item = $location->items()->where('item_id', $inactiveItemId)->first();

        if($item) {
            $location->items()->detach($item);
        }

        $locationItems = $location
            ->items()
            ->paginate($limit);

        return $this->response->paginator($locationItems, new ItemTransformer, ['key' => 'items']);
    }

    public function indexModifiers(Request $request, $locationId)
    {
        $limit = $request->input('limit') ? $request->input('limit') : $this->perPage;

        $location = Location::find($locationId);

        $locationModifiers = $location
            ->modifiers()
            ->paginate($limit);

        if($request->input('filter')){
            $filter = $request->input('filter');

            if(array_key_exists('id', $filter)) {

                $ids = explode(',', $filter['id']);

                if(count($ids)) {
                    $locationModifiers = $location
                        ->modifiers()
                        ->whereIn('modifier_id', $ids)
                        ->paginate($limit);
                }

                return $this->response->paginator($locationModifiers, new ModifierTransformerB, ['key' => 'modifiers']);
            }

            return response()->json(['error' => [
                $this->responseArray(1011,400)
            ]], 400);
        }

        // return collection with paginator
        return $this->response->paginator($locationModifiers, new ModifierTransformerB, ['key' => 'modifiers']);

    }


    public function storeModifiers(Request $request, $locationId)
    {
        $this->getConcept($request,true);
        app('cache')->store('redis')->flush();
        app('cache')->flush();

        $limit = $request->input('limit') ? $request->input('limit') : $this->perPage;

        $location = Location::find($locationId);

        $locationModifiers = $location
            ->modifiers()
            ->paginate($limit);

        $modifiers = $request->input('modifiers', []);

        $locationModifierIds = $location->modifiers()->pluck('modifier_id')->toArray();

        $modifiers = collect($modifiers)->reject(function($value) use($locationModifierIds){
            return in_array($value, $locationModifierIds);
        })->toArray();

        if(count($modifiers)) {

            foreach ($modifiers as $modifier) {
                $modifierExist = Modifier::find($modifier);

                if(!$modifierExist) {
                    continue;
                }

                $location->modifiers()->attach($modifier,['created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString()]);
            }

            $locationModifiers = $location
                ->modifiers()
                ->orderBy('created_at','DESC')
                ->paginate($limit);

            return $this->response->paginator($locationModifiers, new ModifierTransformerB, ['key' => 'modifiers']);
        }

        return $this->response->paginator($locationModifiers, new ModifierTransformerB, ['key' => 'modifiers']);
    }

    public function destroyModifiers(Request $request, $locationId, $inactiveModifierId)
    {
        $this->getConcept($request,true);
        app('cache')->store('redis')->flush();
        app('cache')->flush();

        $limit = $request->input('limit') ? $request->input('limit') : $this->perPage;

        $location = Location::find($locationId);

        $modifier = $location->modifiers()->where('modifier_id', $inactiveModifierId)->first();

        if($modifier) {
            $location->modifiers()->detach($modifier);
        }

        $locationModifiers = $location
            ->modifiers()
            ->paginate($limit);

        return $this->response->paginator($locationModifiers, new ModifierTransformerB, ['key' => 'modifiers']);
    }
}