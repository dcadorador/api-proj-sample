<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Employee;
use App\Api\V1\Models\Bearing;
use App\Api\V1\Models\Order;
use App\Api\V1\Models\Role;
use App\Api\V1\Models\Location;
use Illuminate\Http\Request;
use App\Api\V1\Controllers;
use App\Api\V1\Transformers\EmployeeTransformer;
use App\Api\V1\Transformers\OrderTransformer;
use App\Api\V1\Transformers\BearingTransformer;
use App\Api\V1\Transformers\LocationTransformer;
use App\Api\V1\Services\NotificationService;
use Carbon\Carbon;
use DB;
use App\Jobs\EmployeeBearingJob;

class EmployeeController extends ApiController
{

    public function index(Request $request)
    {
        $concept = $this->getConcept($request);
        $employees = $concept->employees();

        // will add filter for
        $filter = $request->input('filter',[]);
        if(array_key_exists('location',$filter)){
            $location = trim($filter['location']);
            $employees = $employees->whereHas('roles', function($query){
                $query->where('role_id',3);
            });
            $employees = $employees->whereHas('locations', function($query) use ($location) {
               $query->where('location_id',$location);
            });
        }

        if(array_key_exists('email', $filter)) {
            $email = trim($filter['email']);
            $employees = $employees->where('email', $email);
        }

        return $this->response->paginator($employees->orderBy('created_at','DESC')->paginate($this->perPage), new EmployeeTransformer, ['key' => 'employee']);
    }

    public function store(Request $request)
    {
        $validator = app('validator')->make($request->all(),[
            'first-name' => 'required',
            'last-name' => 'required',
            'roles' => 'required',
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => [
                $this->responseArray(1002,400)
            ]], 400);
        }

        $validator = app('validator')->make($request->all(),[
            'username' => 'unique:api_subscribers,username',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => [
                $this->responseArray(1008,400)
            ]], 400);
        }

        // check if password is at least 8 chars with 1 letter/number
        $validator = app('validator')->make($request->all(),[
            'password' => 'min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => [
                $this->responseArray(1019,400)
            ]], 400);
        }


        $employee = new Employee();
        $employee->employee_id = $request->json('employee-id');
        $employee->first_name = $request->json('first-name');
        $employee->last_name = $request->json('last-name');
        $employee->username = $request->json('username');
        $employee->email = $request->json('email');
        $employee->mobile = $request->json('mobile');
        $employee->status = 'active';
        $employee->save();
        $locations = $request->json('locations');
        $roles = $request->json('roles');

        $this->createApiSubscriber(
            'employee',
            $employee->id,
            $request->json('username'),
            $request->json('password'),
            $this->getConcept($request)->client_id
        );

        foreach ($roles as $role)
        {
            $e_role = Role::where('label',strtolower(trim($role)))->first();
            $employee->roles()->attach($e_role->id);
        }

        $employee->concepts()->attach($this->getConcept($request)->id);

        // will add default attachments of all locations for users restaurant, admin, driver
        if(count($locations) > 0 and !is_null($locations)) {
            foreach($locations as $location)
            {
                $employee->locations()->attach($location);
            }
        } else {
            $role_coll = collect($roles);
            if($role_coll->contains('administrator') || $role_coll->contains('restaurant') || $role_coll->contains('driver')) {
                $locations = Location::where('status','active')
                    ->where('concept_id',$this->getConcept($request)->id)
                    ->get();
                if($locations and count($locations) > 0) {
                    foreach($locations as $location) {
                        $employee->locations()->attach($location->id);
                    }
                }
            }
        }

        return $this->response->item($employee, new EmployeeTransformer, ['key' => 'employee'])->setStatusCode(201);
    }

    public function show(Request $request, $employee)
    {
        $employee = Employee::where('id',$employee)->first();

        if(!$employee) {
            return response()->json(['error' => [
                $this->responseArray(1013,404)
            ]], 404);
        }

        return $this->response->item($employee, new EmployeeTransformer, ['key' => 'employee']);
    }

    public function edit(Request $request, $employee)
    {
        $employee = Employee::where('id',$employee)->first();

        if(!$employee) {
            return response()->json(['error' => [
                $this->responseArray(1013,404)
            ]], 404);
        }

        $roles = $request->json('roles',null);
        $api_subscriber = $employee->user()->first();

        if($roles) {
            // check if roles is not array
            if(!is_array($roles)){
                $roles = [$roles];
            }

            $employee->roles()->detach();

            // update the role for the employee
            foreach ($roles as $role)
            {
                if($role && $role != '') {
                    // check if the role is not existing
                    //if(!$employee->hasRole(trim($role))){
                        $e_role = Role::where('label',strtolower(trim($role)))->first();
                        if($e_role) {
                            $employee->roles()->attach($e_role->id);
                        }
                    //}
                }
            }
        }

        if($request->has('employee-id')) {
            $employee->employee_id = $request->input('employee-id');
        }

        if($request->has('first-name')) {
            $employee->first_name = $request->input('first-name');
        }

        if($request->has('last-name')) {
            $employee->last_name = $request->input('last-name');
        }

        if($request->has('username')) {
            $employee->username = $request->input('username');
            $api_subscriber->username = $request->input('username');
        }

        if($request->has('email')) {
            $employee->email = $request->input('username');
        }

        if($request->has('mobile')) {
            $employee->mobile = $request->input('mobile');
        }

        if($request->has('status')) {
            $employee->status = $request->input('status');
        }

        $employee->update();

        if ($request->has('password')) {

            //$api_subscriber = $employee->user()->first();

            $validator = app('validator')->make($request->all(),[
                'password' => 'min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => [
                    $this->responseArray(1019,400)
                ]], 400);
            }

            if($api_subscriber) {
                $api_subscriber->password = trim($request->input('password'));
                $api_subscriber->update();
            }
        }

        // automated null sending of coordinates
        if ($employee->hasRole('driver') && trim($request->json('status')) == 'inactive') {
            $bearing = $employee->bearing()->save(new Bearing([
                'lat' => null,
                'long' => null
            ]));

            $order = $employee->orders()->orderBy('created_at','DESC')->take(1)->first();
            //$ably = New NotificationService();
            //$ably->triggerDriverBearings($employee,$bearing,$order);
            if($order) {
                dispatch(new EmployeeBearingJob($employee,$bearing,$order));
            }
        }

        return $this->response->item($employee, new EmployeeTransformer, ['key' => 'employee']);
    }

    public function getEmpBearings(Request $request, $employee)
    {
        $employee = Employee::with('bearing')
            ->where('id',$employee)->first();

        if(!$employee) {
            return response()->json(['error' => [
                $this->responseArray(1013,404)
            ]], 404);
        }

        $bearing = $employee->bearing()->orderby('created_at','DESC')
            ->take(1)
            ->first();

        if(!$bearing){
            return response()->json(['error' => [
                $this->responseArray(1021,404)
            ]], 404);
        }

        return $this->response->item($bearing, new BearingTransformer(), ['key' => 'bearing']);
    }

    public function setEmpBearings(Request $request, $employee)
    {
        /*if ($request->json('lat') == '' || $request->json('long') == '') {
            return response()->json(['error' => [
                $this->responseArray(1021,400)
            ]], 400);
        }*/

        $employee = Employee::with('bearing')
            ->where('id',$employee)->first();

        if(!$employee) {
            return response()->json(['error' => [
                $this->responseArray(1013,404)
            ]], 404);
        }

        $km_moved = 21;  // Default if driver does not have any bearings yet...
        $old_bearing = $employee->bearing()->orderBy('created_at','DESC')->first();
        if ($old_bearing) {
            $km_moved = $this->distance($old_bearing->lat,$old_bearing->long,$request->json('lat'),$request->json('long'));
        }

        $bearing = null;
        if($km_moved > 20) {
            $bearing = $employee->bearing()->save(new Bearing([
                'lat' => $request->json('lat'),
                'long' => $request->json('long')
            ]));

            if($employee->status == 'active') {
                // get number of orders that are for delivery
                $data = DB::table('orders as c')
                    ->select('c.*')
                    // join order to a table that gets the current order status of the current order
                    ->join(DB::raw("(SELECT MAX(id) max_id, order_id FROM order_order_status GROUP BY order_id) as oos"), function($join){
                        $join->on('oos.order_id','c.id');
                    })
                    ->join('order_order_status','order_order_status.id','=','oos.max_id')
                    ->where('order_order_status.order_status_id','=',30)
                    ->whereIn('c.id', function($query) use ($employee) {
                        $query->select('order_id')->from('employee_order')
                            ->where('employee_id',$employee->id);
                    });


                // if greater than 2 return an error when updating bearings
                if($data->count() > 1){
                    app('log')->debug('Error -> Number of orders for delivery: '.$data->count());
                    return response()->json(['error' => [
                        $this->responseArray(1043,400)
                    ]], 400);
                }

                // if only 1 order is for delivery send the coordinates
                if($data->count() > 0) {
                    $order = Order::find($data->first()->id);
                    app('log')->debug('ORDER:'.$order->id);
                    $status = $order->currentStatus()->first();
                    if ($status) {
                        app('log')->debug('CURRENT STATUS: '.$status->order_status_id);
                        if ($status->order_status_id == 30) {
                            app('log')->debug('FOR DELIVERY, TO SEND IN ABLY');
                            // trigger employee/driver location change
                            //$ably = New NotificationService();
                            //$ably->triggerDriverBearings($employee,$bearing,$order);
                            dispatch(new EmployeeBearingJob($employee,$bearing,$order));
                        }
                    }
                }

            }
        }

        $bearing = $bearing ? $bearing : $old_bearing;

        return $this->response->item($bearing, new BearingTransformer(), ['key' => 'bearing']);
    }

    public function setOrderDriver(Request $request, $employee)
    {
        $employee = Employee::where('id',$employee)->first();
        $order = Order::where('id',$request->json('order'))->first();

        // just made sure that if the order is assigned to the employee no need to assign it again
        // added Nov. 29, 2017
        if( $employee->orders()
                ->where('order_id',$order->id)
                ->where('employee_id',$employee->id)->count() < 1
        )
        {
            $employee->orders()->attach($request->json('order'),['function' => 'driver', 'created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString()]);

            // trigger employee/driver order
            $ably = New NotificationService();
            $ably->triggerDriverOrder($order,$employee);
        }

        return $this->response->item($order, new OrderTransformer(), ['key' => 'order']);
    }

    public function getEmployeeLocations(Request $request,$employee) {

        $employee = Employee::find($employee);

        if(!$employee) {
            return response()->json(['error' => [
                $this->responseArray(1013,404)
            ]], 404);
        }

        $locations = $employee->locations()->paginate($this->perPage);
        return $this->response->paginator($locations, new LocationTransformer, ['key' => 'location']);
    }

    public function getOrders(Request $request, $employee) {

        // get the filter if there are
        $filter = $request->input('filter',[]);

        $employee = $this->getConcept($request)->employees()->where('id',$employee)->first();

        $orders = $employee->orders()->orderBy('created_at','DESC');

        if(array_key_exists('status',$filter)){
            $status = trim($filter['status']);
            if(!$employee->roles->contains('id',3)) {
                $orders = Order::from('orders as c')
                    ->select('c.*')
                    // join order to a table that gets the current order status of the current order
                    ->join(DB::raw("(SELECT MAX(id) max_id, order_id FROM order_order_status GROUP BY order_id) as oos"), function($join){
                        $join->on('oos.order_id','c.id');
                    })
                    ->join('order_order_status','order_order_status.id','=','oos.max_id')
                    ->whereIn('order_order_status.order_status_id',function($query) use ($status){
                        $query->select('id')->from('order_statuses')
                            ->orWhere('order_statuses.code',$status)
                            ->orWhere('order_statuses.type',$status);
                    })
                    ->whereIn('c.location_id', function($query) use ($employee) {
                        $query->select('location_id')->from('employee_location')
                            ->where('employee_id',$employee->id);
                    })->orderBy('c.created_at','DESC');
            } else {
                $orders = Order::from('orders as c')
                    ->select('c.*')
                    // join order to a table that gets the current order status of the current order
                    ->join(DB::raw("(SELECT MAX(id) max_id, order_id FROM order_order_status GROUP BY order_id) as oos"), function($join){
                        $join->on('oos.order_id','c.id');
                    })
                    ->join('order_order_status','order_order_status.id','=','oos.max_id')
                    ->whereIn('order_order_status.order_status_id',function($query) use ($status){
                        $query->select('id')->from('order_statuses')
                            ->orWhere('order_statuses.code',$status)
                            ->orWhere('order_statuses.type',$status);
                    })
                    ->whereIn('c.id', function($query) use ($employee) {
                        $query->select('order_id')->from('employee_order')
                            ->where('employee_id',$employee->id);
                    })->orderBy('c.created_at','DESC');
            }
        }

        return $this->response->paginator($orders->paginate($this->perPage), new OrderTransformer, ['key' => 'order']);
    }

    public function getOrderEmployee(Request $request, $order) {
        $order = Order::where('id',$order)
            ->first();

        if(!$order){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $filter = $request->input('filter',[]);
        if(array_key_exists('function', $filter) && $filter['function'] == 'driver') {
            $employees = $order->driver()->orderBy('created_at','DESC')->paginate($this->perPage);
        } else {
            $employees = $order->employees()->orderBy('created_at','DESC')->paginate($this->perPage);
        }

        return $this->response->paginator($employees, new EmployeeTransformer, ['key' => 'employee']);
    }

    /* From https://stackoverflow.com/questions/29711728/how-to-sort-geo-points-according-to-the-distance-from-current-location-in-androi */
    private function distance($fromLat, $fromLon, $toLat, $toLon) {
        $radius = 6378137;   // approximate Earth radius, *in meters*
        $deltaLat = $toLat - $fromLat;
        $deltaLon = $toLon - $fromLon;
        $angle = 2 * asin( sqrt(
                pow(sin($deltaLat/2), 2) +
                cos($fromLat) * cos($toLat) *
                pow(sin($deltaLon/2), 2) ) );
        return $radius * $angle; // convert into km
    }
}