<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Item;
use App\Api\V1\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Api\V1\Models\TimedEvent;
use App\Api\V1\Transformers\TimedEventTransformer;
use App\Api\V1\Transformers\ItemTransformer;
use Carbon\Carbon;

class TimedEventController extends ApiController
{
    public function index(Request $request)
    {
        $concept = $this->getConcept($request);
        $timed_events = TimedEvent::where('concept_id',$concept->id);

        $filter = $request->input('filter',[]);

        if(array_key_exists('active',$filter)){
            $is_active = trim($filter['active']);
            $timed_events->where('is_active',$is_active);
        }

        if(array_key_exists('from',$filter) and array_key_exists('to',$filter)){
            $from = trim($filter['from']);
            $to = trim($filter['to']);
            $timed_events->where('created_at','>=',Carbon::parse($from)->format('Y-m-d').' 00:00:00')
                ->where('created_at','<=',Carbon::parse($to)->format('Y-m-d').' 23:59:59');
        }

        if(array_key_exists('label',$filter)){
            $label = trim($filter['label']);
            if (strpos(trim($label), ',')) {
                $_label = explode(',', $label);
                $timed_events->where(function($query) use ($_label){
                    foreach ($_label as $value) {
                        $query->orWhere('label', 'like', "%{$value}%");
                    }
                });
            } elseif(strpos(trim($label), ' ')) {
                $_label = preg_split('/\s+/', $label, -1, PREG_SPLIT_NO_EMPTY);
                $timed_events->where(function($query) use ($_label){
                    foreach ($_label as $value) {
                        $query->orWhere('label', 'like', "%{$value}%");
                    }
                });
            } else {
                $timed_events->where('label', 'like', "%{$label}%");
            }
        }

        $data = $timed_events->paginate($this->perPage)->appends(app('request')->except('page'));
        return $this->response->paginator($data, new TimedEventTransformer, ['key' => 'timed-event']);
    }

    public function show(Request $request,$timedEvent)
    {
        $concept = $this->getConcept($request);
        $timed_event = TimedEvent::where('concept_id',$concept->id)
                        ->where('id',$timedEvent)
                        ->first();

        if(!$timed_event){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        return $this->response->item($timed_event, new TimedEventTransformer, ['key' => 'timed-event']);
    }

    public function store(Request $request)
    {
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
        $concept = $this->getConcept($request,true);
        $timed_event = new TimedEvent();
        $timed_event->concept_id = $concept->id;
        $timed_event->label = $request->input('label',null);
        $timed_event->is_active = $request->input('is-active',1);
        $timed_event->value = $request->input('value',0);
        $timed_event->from_date = $request->input('from-date');
        $timed_event->to_date = $request->input('to-date');
        $event_times = $request->input('event-times',null);
        $timed_event->event_times =  $event_times ? json_encode($event_times) : null;
        $timed_event->save();

        return $this->response->item($timed_event, new TimedEventTransformer, ['key' => 'timed-event']);
    }

    public function edit(Request $request, $timedEvent)
    {
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
        $concept = $this->getConcept($request,true);
        $timed_event = TimedEvent::where('concept_id',$concept->id)
            ->where('id',$timedEvent)
            ->first();

        if(!$timed_event){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        if ($request->has('label')) {
            $timed_event->label = $request->input('label');
        }
        if ($request->has('is-active')) {
            $timed_event->is_active = $request->input('is-active');
        }
        if ($request->has('value')) {
            $timed_event->value = $request->input('value');
        }
        if ($request->has('from-date')) {
            $timed_event->from_date = $request->input('from-date');
        }
        if ($request->has('to-date')) {
            $timed_event->to_date = $request->input('to-date');
        }
        if ($request->has('event-times')) {
            $timed_event->event_times = json_encode($request->input('event-times'));
        }

        $timed_event->update();
        return $this->response->item($timed_event, new TimedEventTransformer, ['key' => 'timed-event']);
    }

    public function getItems(Request $request, $timedEvent)
    {
        $concept = $this->getConcept($request);
        $timed_event = TimedEvent::where('concept_id',$concept->id)
            ->where('id',$timedEvent)
            ->first();

        if(!$timed_event){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        $items = $timed_event->items();
        // check if there is a code filter
        $filter = $request->input('filter',[]);

        // filter based on the item code
        if(array_key_exists('code', $filter)) {
            $items->where('items.code',$filter['code']);
        } elseif(array_key_exists('id', $filter)) {
            $items->where('items.id',$filter['id']);
        }

        return $this->response->paginator($items->paginate($this->perPage), new ItemTransformer, ['key' => 'item']);
    }

    public function storeItem(Request $request, $timedEvent)
    {
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
        $concept = $this->getConcept($request,true);
        $timed_event = TimedEvent::where('concept_id',$concept->id)
            ->where('id',$timedEvent)
            ->first();

        if(!$timed_event){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        if(is_array($request->input('items'))) {
            $item_array = $request->input('items');
            foreach($item_array as $item) {
                $cacheKey = md5($item);
                if(app('cache')->has($cacheKey)) {
                    app('cache')->forget($cacheKey);
                }
                $timed_event->items()->attach($item,['created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString()]);
            }
        } else {
            $cacheKey = md5($request->input('items'));
            if(app('cache')->has($cacheKey)) {
                app('cache')->forget($cacheKey);
            }
            $timed_event->items()->attach($request->input('items'),['created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(), 'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString()]);
        }

        return $this->response->paginator($timed_event->items()->paginate($this->perPage), new ItemTransformer, ['key' => 'item']);
    }

    public function getMenuTimedEvents(Request $request, $menu)
    {
        $filter = $request->input('filter', []);

        $menu = Menu::find($menu);

        if(!$menu){
            return response()->json(['error' => [
                $this->responseArray(1022,404)
            ]], 404);
        }

        // get all categories of menu
        $categoryIds = $menu->categories()
            ->pluck('id')
            ->toArray();

        // get all child items of category
        $items = Item::whereIn('category_id', $categoryIds)
            ->pluck('id')
            ->toArray();

        if(array_key_exists('items', $filter)) {
            $userItemIds = explode(',', $filter['items']);

            // only retain item ids that are related to category above
            $items = collect($userItemIds)->filter(function($value) use ($items){
                return in_array($value, $items);
            });
        }

        $timed_events = TimedEvent::whereHas('items', function($q) use($items){
            $q->whereIn('item_id', $items);
        });

        return $this->response->paginator($timed_events->paginate($this->perPage), new TimedEventTransformer, ['key' => 'timed-event']);
    }


}