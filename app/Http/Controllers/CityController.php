<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\City;
use App\Api\V1\Models\Location;
use App\Api\V1\Models\DeliveryArea;
use App\Api\V1\Transformers\CityTransformer;
use App\Api\V1\Transformers\DeliveryAreaTransformer;
use Illuminate\Http\Request;
use App\Api\V1\Controllers;

class CityController extends ApiController
{
    public function index(Request $request)
    {
        $city = City::paginate($this->perPage);

        return $this->response->paginator($city, new CityTransformer(), ['key' => 'city']);
    }

    public function show(Request $request, $city)
    {
        $city = City::where('id',$city)->first();

        if(!$city) {
            return response()->json(['error' => [
                $this->responseArray(1060,404)
            ]], 404);
        }

        return $this->response->item($city, new CityTransformer, ['key' => 'city']);

    }

    public function store(Request $request)
    {
        $city = new City();
        $city->name = $this->getLocalizedInput($request, 'name');
        $city->save();

        return $this->response->item($city, new CityTransformer, ['key' => 'city']);
    }


    public function getDeliveryAreas(Request $request, $city)
    {
        $concept = $this->getConcept($request);
        $locations = Location::where('city_id',$city)
            ->where('concept_id',$concept->id)
            ->pluck('id');

        if(count($locations) < 1) {
            return response()->json(['error' => [
                $this->responseArray(1010,404)
            ]], 404);
        }

        $delivery_areas = DeliveryArea::whereIn('location_id',$locations)
            ->paginate($this->perPage);

        if(count($delivery_areas) < 1) {
            return response()->json(['error' => [
                $this->responseArray(1010,404)
            ]], 404);
        }

        return $this->response->paginator($delivery_areas, new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
    }
}