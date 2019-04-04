<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\City;
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
use App\Api\V1\Helpers\AuthHelper;
use Carbon\Carbon;

class LocationController extends ApiController
{
    private function _closeOpenPolygon($string)
    {
        $datas = explode(',',rtrim($string, ","));
        $firstCoordinates = array_slice($datas,0,2);
        $lastCoordinates = array_slice($datas,-2,2);
        $starting = array(ltrim($firstCoordinates['0'],'['),rtrim($firstCoordinates['1'],']'));
        $closing = array(ltrim($lastCoordinates['0'],'['),rtrim($lastCoordinates['1'],']'));

        if ($starting != $closing)
        {
            $string .= '['.implode(",",$starting).']';
        }

        return $string;
    }

    public function show($location)
    {
        $location = Location::find($location);

        if(!$location) {
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
        }

        return $this->response->item($location, new LocationTransformer, ['key' => 'location']);
    }

    public function index(Request $request)
    {
        $concept = $this->getConcept($request)->id;
        $limit = $request->input('limit') ? $request->input('limit') : $this->perPage;
        $locations = Location::where('concept_id', $concept)
                    ->where('status','active')
                    ->paginate($limit);

        if($request->input('_lat') && $request->input('_long')) {
            $lat = $request->input('_lat');
            $long = $request->input('_long');
            if (is_numeric($lat) && is_numeric($long)) {
                $locations = $this->getLocationsSortedByDistance($concept, $lat, $long);
                return $this->response->collection(Collection::make($locations), new LocationTransformerB, ['key' => 'location']);
            }
        }


        if($request->input('filter')){
            $filter = $request->input('filter');

            if(array_key_exists('enabled',$filter)) {
                if($filter['enabled'] == 'all') {
                    $locations = Location::where('concept_id', $concept)
                        ->paginate($limit);
                } elseif ($filter['enabled'] == 'inactive') {
                    $locations = Location::where('concept_id', $concept)
                        ->where('status','inactive')
                        ->paginate($limit);
                } else {
                    $locations = Location::where('concept_id', $concept)
                        ->where('status','active')
                        ->paginate($limit);
                }
                return $this->response->paginator($locations, new LocationTransformer, ['key' => 'location']);
            }

            if(array_key_exists('is-open',$filter)){
                $locations = Location::where('concept_id', $concept)
                    ->where('status','active')
                    ->get();

                $open_loc = [];
                foreach($locations as $location) {
                    if($location->is_open){
                        $open_loc[] = $location;
                    }
                }

                if(count($open_loc) == 0) {
                    return response()->json(['error' => [
                        $this->responseArray(1009,404)
                    ]], 404);
                }

                return $this->response->collection(Collection::make($open_loc), new LocationTransformer, ['key' => 'location']);
            }

            if(!array_key_exists('delivery-area.lat',$filter) || !array_key_exists('delivery-area.long',$filter)){
                return response()->json(['error' => [
                    $this->responseArray(1025,400)
                ]], 400);
            }

            $lat = $filter['delivery-area.lat'];
            $long = $filter['delivery-area.long'];

            $location = $this->locationsLookup($concept,$lat,$long);

            if($location){
                return $this->response->item($location, new LocationTransformer, ['key' => 'location']);
            }

            // added the insertion of unsupported locations
            $customer = AuthHelper::getAuthenticatedUser();
            app('db')->table('unsupported_locations')->insert([
                'customer_id' => $customer ? $customer->id : null,
                'latitude' => $lat,
                'longitude' => $long,
                'concept_id' => $concept,
                'created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(),
                'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(),
            ]);

            if(array_key_exists('city',$filter)){
                $locations = Location::where('concept_id', $concept)
                    ->where('status','active')
                    ->where('city_id',$filter['city'])
                    ->paginate($limit);

                return $this->response->paginator($locations, new LocationTransformer, ['key' => 'location']);
            }

            return response()->json(['error' => [
                $this->responseArray(1011,400)
            ]], 400);
        }
        
        // return collection with paginator
        return $this->response->paginator($locations, new LocationTransformer, ['key' => 'location']);
    }

    public function store(Request $request)
    {

        $concept = $this->getConcept($request,true);

        $location = new Location();
        $location->concept_id = $concept->id;
        $location->status = $request->input('status', 'active');
        $location->code = $request->input('code', null);
        $location->name = $this->getLocalizedInput($request, 'name');
        $location->telephone = $request->input('telephone');
        $location->email = $request->input('email');
        $location->line1 = $request->input('line1',null);
        $location->line2 = $request->input('line2',null);
        $location->lat = $request->input('lat');
        $location->long = $request->input('long');
        $location->country = $request->input('country', $concept->country);
        $location->pos = $request->input('pos', $concept->default_pos);
        $location->delivery_charge = $request->input('delivery-charge', $concept->default_delivery_charge);
        $location->delivery_enabled = $request->input('delivery-enabled',1);
        $location->opening_hours = $request->has('opening-hours')? json_encode($request->json('opening-hours')): $concept->default_opening_hours;
        $location->city_id = $request->input('city',null);

        $promised_time_delta = $request->json('promised-time-delta');

        $location->promised_time_delta_delivery = $promised_time_delta['delivery']? $promised_time_delta['delivery']: $concept->default_promised_time_delta_delivery;
        $location->promised_time_delta_pickup = $promised_time_delta['pickup']? $promised_time_delta['pickup']: $concept->default_promised_time_delta_pickup;
        $location->save();
        app('log')->debug('CREATED A LOCATION :'.json_encode($location));
        return $this->response->item($location, new LocationTransformer, ['key' => 'location']);
    }

    public function edit(Request $request, $location)
    {
        $this->getConcept($request,true);

        $location = Location::find($location);

        if ($location === null) {
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
        }

        if ($request->has('status')) {
            $location->status = $request->input('status');
        }

        if ($request->has('name')) {
            $location->name = $this->getLocalizedInput($request, 'name');
        }

        if ($request->has('pos')) {
            $location->pos = $request->input('pos');
        }

        if ($request->has('telephone')) {
            $location->telephone = $request->input('telephone');
        }

        if ($request->has('email')) {
            $location->email = $request->input('email');
        }

        if ($request->has('lat')) {
            $location->lat = $request->input('lat');
        }

        if ($request->has('line1')) {
            $location->line1 = $request->input('line1');
        }

        if ($request->has('line2')) {
            $location->line2 = $request->input('line2');
        }

        if ($request->has('long')) {
            $location->long = $request->input('long');
        }

        if ($request->has('country')) {
            $location->country = $request->input('country');
        }

        if ($request->has('city')) {
            $location->city_id = $request->input('city');
        }

        if ($request->has('promised-time-delta')) {
            $promised_time = json_decode(json_encode($request->input('promised-time-delta')));
            $location->promised_time_delta_delivery = $promised_time->delivery;
            $location->promised_time_delta_pickup = $promised_time->pickup;
        }

        if ($request->has('opening-hours')) {
            $opening_hours = $request->json('opening-hours');
            $location->opening_hours = json_encode($opening_hours);
        }

        if ($request->has('delivery-enabled')) {
            $location->delivery_enabled = $request->input('delivery-enabled');
        }

        $location->update();

        return $this->response->item($location, new LocationTransformer, ['key' => 'location']);
    }


    public function getDevices(Request $request, $location) 
    {
        $location = Location::find($location);

	    // throw 404 exception if resource does not exists
	    // this will be converted to a jsonapi error by dingo
	    if ($location === null) {
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
	    }

	    return $this->response->paginator($location->devices()->paginate($this->perPage), new DeviceTransformer, ['key' => 'device']);
    }

    public function getDeliveryAreas(Request $request, $location)
    {
        $concept = $this->getConcept($request,true);

        $location = Location::with('areas')
            ->where('id',$location)
            ->where('concept_id',$concept->id)
            ->first();

        if ($location === null) {
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
        }

        $areas = $location->areas();

        return $this->response->paginator($areas->paginate($this->perPage), new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
    }

    public function getDeliveryAreasByCity(Request $request)
    {
        $concept = $this->getConcept($request,true);

        $locations = Location::with('areas')
            ->where('concept_id',$concept->id)
            ->pluck('id');

        if ($locations === null) {
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
        }

        $areas = DeliveryArea::whereIn('location_id',$locations);

        if($request->input('filter')){
            $filter = $request->input('filter',[]);
            if(array_key_exists('city',$filter)){
                $city = $filter['city'];
                $location_ids = Location::where('concept_id',$concept->id)
                    ->where('city_id',$city)
                    ->pluck('id');
                $areas = DeliveryArea::whereIn('location_id',$location_ids);
                return $this->response->paginator($areas->paginate($this->perPage), new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
            }
        }

        return $this->response->paginator($areas->paginate($this->perPage), new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
    }

    public function setDeliveryAreas(Request $request, $location)
    {
        $concept = $this->getConcept($request,true);

        $location = Location::with('areas')
            ->where('id',$location)
            ->where('concept_id',$concept->id)
            ->first();

        $label = $request->json('label');
        $name = $this->getLocalizedInput($request, 'name');
        $coordinates = $request->json('coordinates');
        $string = "";

        // return if area label is existing
        if(!$location) {
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
        }

        //
        for ($i = 0; $i < count($coordinates) || $i < count($coordinates); $i++)
        {
            // assign values
            $coordinatesArr = array_key_exists($i, $coordinates) ? implode(',',$coordinates[$i]) : '';
            $string .= '['.$coordinatesArr.'],';
        }

        $newCoordinates = $this->_closeOpenPolygon($string);

        do {
            $newCoordinates = rtrim($newCoordinates,",");
        } while (substr($newCoordinates, -1) == ',');

        $location->areas()->save(new DeliveryArea([
            'label' => $label,
            'name' => $name,
            'coordinates' => $newCoordinates
        ]));

        return $this->response->paginator($location->areas()->paginate($this->perPage), new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
    }

    public function deleteDeliveryArea(Request $request, $location, $area)
    {
        $concept = $this->getConcept($request,true);

        $location = Location::with('areas')
            ->where('id',$location)
            ->where('concept_id',$concept->id)
            ->first();

        $object = $location->areas->where('id',$area)
                    ->first();
        $object->delete();

        return $this->response->paginator($location->areas()->paginate($this->perPage), new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
    }

    public function editDeliveryArea(Request $request, $location, $area)
    {
        $concept = $this->getConcept($request,true);

        $location = Location::with('areas')
            ->where('id',$location)
            ->where('concept_id',$concept->id)
            ->first();

        $area = $location->areas->where('id',$area)
            ->first();

        $label = $request->json('label');
        $name = $this->getLocalizedInput($request, 'name');
        $coordinates = $request->json('coordinates');
        $string = "";

        for ($i = 0; $i < count($coordinates) || $i < count($coordinates); $i++)
        {
            // assign values
            $coordinatesArr = array_key_exists($i, $coordinates) ? implode(',',$coordinates[$i]) : '';
            $string .= '['.$coordinatesArr.'],';
        }

        $newCoordinates = $this->_closeOpenPolygon($string);

        // added this for location fix
        do {
            $newCoordinates = rtrim($newCoordinates,",");
        } while (substr($newCoordinates, -1) == ',');

        $area->label = $label ? $label : $area->label;
        $area->coordinates = $newCoordinates;
        $area->name = $name;
        $area->update();

        return $this->response->item($area, new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
    }

    public function getDeliveryArea(Request $request, $location, $area)
    {
        $deliveryArea = DeliveryArea::find($area);

        if (!$deliveryArea) {
            return response()->json(['error' => [
                $this->responseArray(1010,404)
            ]], 404);
        }

        return $this->response->item($deliveryArea, new DeliveryAreaTransformer(), ['key' => 'delivery-area']);
    }



    public function setLocationGeofence(Request $request, $location)
    {
        $concept = $this->getConcept($request,true);

        $location = Location::where('id',$location)
            ->where('concept_id',$concept->id)
            ->first();

        $inner = $request->json('inner');
        $outer = $request->json('outer');
        $innerString = "";
        $outerString = "";

        for ($i = 0; $i < count($inner) || $i < count($inner); $i++)
        {
            // assign values
            $coordinatesArrInner = array_key_exists($i, $inner) ? implode(',',$inner[$i]) : '';
            $innerString .= '['.$coordinatesArrInner.'],';
        }

        for ($i = 0; $i < count($outer) || $i < count($outer); $i++)
        {
            // assign values
            $coordinatesArrOuter = array_key_exists($i, $outer) ? implode(',',$outer[$i]) : '';
            $outerString .= '['.$coordinatesArrOuter.'],';
        }

        $location->geofence()->save(new Geofence([
            'inner' => $innerString,
            'outer' => $outerString
        ]));

        return $this->response->item($location->geofence()->first(), new LocationGeofenceTransformer(), ['key' => 'geofence']);
    }

    public function getLocationGeofence(Request $request, $location)
    {
        $concept = $this->getConcept($request);

        $location = Location::with('geofence')
            ->where('id',$location)
            ->where('concept_id',$concept->id)
            ->first();

        return $this->response->item($location->geofence()->first(), new LocationGeofenceTransformer(), ['key' => 'geofence']);
    }


    private function locationsLookup($concept,$lat,$long)
    {
        $point = new Point($lat,$long);
        $foundStore = null;

        Location::with('areas')->where('status','active')
            ->where('concept_id',$concept)
            ->chunk(20,function($locations)use($point,&$foundStore){
                foreach($locations as $location) {
                    $areas = $location->areas;

                    $found = false;
                    if($areas){
                        foreach($areas as $area){
                            $coordinates = $area->coordinates;
                            $polygonCoordinates = (json_decode('['. $coordinates .']'));

                            $points = [];
                            foreach($polygonCoordinates as $coor){
                                $x = $coor[0];
                                $y = $coor[1];
                                $points[]= new Point($x,$y);
                            }

                            $pls = new PointLocationService($points);
                            if($pls->pointInPolygon($point) !== PointLocationService::OUTSIDE){
                                $foundStore = [
                                    'store' => $location,
                                    'storeArea' => $area
                                ];
                                $found = true;
                                break;
                            }
                        }
                        if($found){
                            break;
                        }
                    }
                }
            });

        if($foundStore){
            $location = $foundStore['store'];
            return $location;
        }

        return false;
    }

    private function getLocationsSortedByDistance($concept, $lat, $long) {
        $hash = array();
        Location::where('concept_id', $concept)->where('status', 'active')->chunk(20, function($locations) use ($lat, $long, &$hash) {
            foreach ($locations as $location) {
                $distance = $this->distance($lat, $long, $location->lat, $location->long);
                $location->distance = $distance;
                $hash[$distance] = $location;
            }
        });
        ksort($hash);
        //return $hash;
        return array_values($hash);
    }

    /* From https://stackoverflow.com/questions/29711728/how-to-sort-geo-points-according-to-the-distance-from-current-location-in-androi */
    private function distance($fromLat, $fromLon, $toLat, $toLon) {
        /*$radius = 6378137;   // approximate Earth radius, *in meters*
        $deltaLat = $toLat - $fromLat;
        $deltaLon = $toLon - $fromLon;
        $angle = 2 * asin( sqrt(
                pow(sin($deltaLat/2), 2) +
                        cos($fromLat) * cos($toLat) *
                                pow(sin($deltaLon/2), 2) ) );
        return $radius * $angle;*/
        $R = 6371; // km
        $dLat = deg2rad($fromLat-$toLat);
        $dLon = deg2rad($fromLon-$toLon);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = $R * $c * 1000;
        return $d;
    }
}