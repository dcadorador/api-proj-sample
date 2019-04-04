<?php

namespace App\Api\V1\Controllers;

use DB;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Api\V1\Models\Order;
use App\Api\V1\Transformers\OrderTransformer;



class ReportController extends ApiController
{

    public function deliveryAreas(Request $request) {
        $concept = $this->getConcept($request);
        $days = $request->input('days', 7);
        $deliveries = DB::table('orders')
                        ->join('customer_address', 'orders.customer_address_id', '=', 'customer_address.id')
                        ->selectRaw('lat, `long`')
                        ->whereRaw('orders.type = \'deliver\'')
                        ->whereRaw('orders.created_at BETWEEN DATE_SUB(NOW(), INTERVAL ? DAY) AND NOW()', [$days])
                        ->whereRaw('orders.concept_id = '.$concept->id)
                        ->get();
        return response()->json($deliveries);
    }

    public function dailySales(Request $request) {
        $concept = $this->getConcept($request);
        $days = $request->input('days', 7);
        $sales = DB::table('orders')
                    ->join('order_order_status', function ($join) {
                            $join->on('orders.id', '=', 'order_order_status.order_id')
                                    ->latest()
                                    ->limit(1)
                                    ->where('order_status_id', '=', 32);
                            })
                    ->selectRaw('DATE(orders.created_at) AS date, count(*) as number, SUM(total) AS total_sales')
                    ->whereRaw('orders.created_at BETWEEN DATE_SUB(NOW(), INTERVAL ? DAY) AND NOW()', [$days])
                    ->whereRaw('orders.is_posted = 1')
                    ->whereRaw('orders.concept_id = '.$concept->id)
                    ->groupBy('date')
                    ->get();
        return response()->json($sales);
    }

    public function threeDaySummary(Request $request) {
        $concept = $this->getConcept($request);
        $interval = $request->input('interval', 0);
        $sales = DB::table('orders')
                    ->join('order_order_status', function ($join) {
                            $join->on('orders.id', '=', 'order_order_status.order_id')
                                    ->latest()
                                    ->limit(1)
                                    ->where('order_status_id', '=', 32);
                            })
                    ->selectRaw("DATEDIFF(CURDATE(),STR_TO_DATE(orders.created_at, '%Y-%m-%d')) as ival, DATE(orders.created_at) AS date, source, type, payment_type, count(*) as number, sum(total) as total_sales")
                    ->whereRaw('orders.created_at BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 DAY) and CURDATE()+1')
                    ->whereRaw('orders.is_posted = 1')
                    ->whereRaw('orders.concept_id = '.$concept->id)
                    ->groupBy('ival')
                    ->groupBy('source')
                    ->groupBy('type')
                    ->groupBy('payment_type')
                    ->get();
        return response()->json($sales);
    }

    public function overdueOrders(Request $request) {
        $concept = $this->getConcept($request);
        $orders = DB::table('orders')
                        ->selectRaw('orders.id, orders.code, reference, promised_time, orders.type, total, location_id, customer_id, customers.first_name, customers.mobile as mobile, translations.value as current_status_delivery, translations2.value as current_status_pickup')
                        ->join('customers', 'orders.customer_id', 'customers.id')
                        ->join('order_order_status', function ($join) {
                            $join->on('orders.id', '=', 'order_order_status.order_id')
                                    ->latest()
                                    ->limit(1)
                                    ->where('order_status_id', '<=', 31)
                                    ->where('order_status_id', '>=', 25);
                            })
                        ->join('order_statuses', 'order_order_status.order_status_id', 'order_statuses.id')
                        ->join('translations', function ($join) {
                            $join->on('order_statuses.delivery_description', '=', 'translations.group_id')
                                    ->where('locale', '=', app('translator')->getLocale());
                            })
                        ->join('translations as translations2', function ($join) {
                            $join->on('order_statuses.pickup_description', '=', 'translations2.group_id')
                                ->where('translations2.locale', '=', app('translator')->getLocale());
                        })
                        ->where('concept_id', $concept->id)
                        ->where('promised_time', '<', 'now()')
                        ->where('orders.created_at', '>', 'CURDATE()-1')
                        ->orderBy('promised_time', 'asc')
                        ->limit(10)
                        ->get();
       
        return response()->json($orders);
    }

    private function locations(Request $request) {

    }
}