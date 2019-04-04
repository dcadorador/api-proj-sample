<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Device;
use App\Api\V1\Models\Location;
use App\Api\V1\Models\ApiSubscriber;
use App\Api\V1\Transformers\DeviceTransformer;

class DeviceController extends ApiController
{

    public function show($device)
    {
        $device = Device::find($device);

        if(!$device){
            return response()->json(['error' => [
                $this->responseArray(1024,404)
            ]], 404);
        }


        return $this->response->item($device, new DeviceTransformer, ['key' => 'device']);
    }

    public function index($location) 
    {
        $location = Location::find($location);

        if(!$location){
            return response()->json(['error' => [
                $this->responseArray(1009,404)
            ]], 404);
        }
        $devices = $location->devices()->paginate($this->perPage);
        
        // return collection with paginator
        return $this->response->paginator($devices, new DeviceTransformer, ['key' => 'device']);
    }

    public function store(Request $request, $location)
    {
        $concept = $this->getConcept($request);
        $apiSubscriber = ApiSubscriber::where('username', $request->input('device-uuid'))
            ->where('client_id',$concept->client_id)
            ->first();

        if ($apiSubscriber != null) {
            // Error: MAC already exists
            return response()->json(['error' => [
                $this->responseArray(1054,400)
            ]], 400);
        }

        $location = Location::findOrFail($location);

        $device = new Device();
        $device->location_id = $location->id;
        $device->label = $request->input('label');
        $device->device_uuid = $request->input('device-uuid');
        $device->save();

        $this->createApiSubscriber('device', 
                                    $device->id, 
                                    $request->input('device-uuid'),
                                    $request->input('device-uuid'),
                                    $this->getConcept($request)->client_id);

        return $this->response->created($device->uri);
    }

    public function patch(Request $request, $device)
    {
        $device = Device::find($device);

        if(!$device){
            return response()->json(['error' => [
                $this->responseArray(1024,404)
            ]], 404);
        }

        $device->label = $request->input('label', $device->label);
        $device->tasks = $request->input('tasks', $device->tasks);

        $device->save();

        return $this->response->noContent();
    }

    public function heartbeat(Request $request, $device) {
        $device = Device::find($device);

        if(!$device){
            return response()->json(['error' => [
                $this->responseArray(1024,404)
            ]], 404);
        }

        $device->last_heartbeat_at = new \DateTime;
        $tasks = $device->tasks;
        $device->tasks = '';
        $device->save();
        return $this->response->created()->withHeader('Solo-Tasks', $tasks);
    }

}