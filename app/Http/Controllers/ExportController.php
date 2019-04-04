<?php

namespace App\Api\V1\Controllers;


use Illuminate\Http\Request;

use App\Api\V1\Models\Export;
use App\Api\V1\Models\Customer;
use League\Csv\Reader;
use League\Csv\Writer;
use Carbon\Carbon;
use App\Api\V1\Models\Order;
use App\Api\V1\Transformers\ExportTransformer;
use App\Api\V1\Models\OrderOrderStatus;

class ExportController extends ApiController
{
    public function exportData(Request $request)
    {
        $concept = $this->getConcept($request);

        if(!$concept) {
            return response()->json(['error' => [
                $this->responseArray(1014,404)
            ]], 404);
        }

        $type = $request->input('type');

        if($type == 'customers') {
            // get the list of users
            $customers = Customer::whereHas('concepts', function ($q) use ($concept) {
                $q->where('concept_id', $concept->id);
            })->orderBy('created_at', 'DESC')
                ->select(['id', 'first_name', 'last_name', 'email', 'mobile', 'status', 'account_type', 'created_at', 'updated_at'])
                ->get();

            //set columns
            $columns = [
                'ID',
                'FIRST_NAME',
                'LAST_NAME',
                'EMAIL',
                'MOBILE',
                'STATUS',
                'ACCOUNT_TYPE',
                'CREATED_AT',
                'UDPATED_AT'
            ];

            $filename = storage_path() . '/exports/customers_' . strtolower($concept->label) . '_' . Carbon::now()->format('Ymd_His') . '.csv';

            $writer = Writer::createFromFileObject(new \SplTempFileObject());
            $writer->insertOne($columns);

            foreach ($customers as $customer) {
                $data = $customer->toArray();
                $data['mobile'] = '"'.$data['mobile'].'"';
                $writer->setOutputBOM(Reader::BOM_UTF8);
                $writer->insertOne($data);
            }

            file_put_contents($filename, $writer->newReader());

            $csv_url = $this->saveUploadedExport($filename);

            // add the new export model
            $export = new Export();
            $export->type = $type;
            $export->csv_uri = $csv_url;
            $export->concept_id = $concept->id;
            $export->save();

            try {
                unlink($filename);
            } catch (\Exception $e) {
                app('log')->error('Failed to delete the file: ' . $e->getMessage());
            }

            return $this->response->item($export, new ExportTransformer, ['key' => 'export']);

        }

        $orders = Order::where('concept_id',$concept->id)
            ->orderBy('created_at','DESC')
            ->select([
                'id',
                'code',
                'promised_time',
                'source',
                'type',
                'payment_type',
                'subtotal',
                'coupon_code',
                'delivery_charge',
                'discount',
                'total',
                'customer_favorite',
                'scheduled_time',
                'is_posted',
                'order_pos_response',
                'created_at',
                'customer_id',
                'location_id'
            ])
            ->get();

        $order_data = [];
        //set columns
        $columns = [
            'ID',
            'ORDER_HID',
            'PROMISED_TIME',
            'SOURCE',
            'TYPE',
            'PAYMENT_TYPE',
            'SUBTOTAL',
            'COUPON_CODE',
            'DELIVERY_CHARGE',
            'DISCOUNT',
            'TOTAL',
            'CUSTOMER_FAVORITE',
            'SCHEDULED_TIME',
            'IS_POSTED',
            'ORDER_POS_RESPONSE',
            'CREATED_AT',
            'CUSTOMER_ID',
            'CUSTOMER_NAME',
            'CUSTOMER_PHONE_NUMBER',
            'LOCATION_ID',
            'LOCATION_NAME',
            'CURRENT_ORDER_STATUS',
            'DRIVER_CODE',
            'DRIVER'
        ];

        foreach($orders as $order)
        {
            $tmp_arr = $order->toArray();
            unset($tmp_arr['location_id']); // todo have to make this better!!!!
            $tmp_arr['customer_name'] = $order->customer ? ucwords($order->customer->first_name).' '.ucwords($order->customer->last_name) : null;
            $tmp_arr['customer_phone_number'] = $order->customer ? '"'.$order->customer->mobile.'"' : null;
            $tmp_arr['location_id'] = $order->location->id;
            $tmp_arr['location_name'] = $order->location ? $order->location->translate('en-us')->name : null;
            $current_status = OrderOrderStatus::with('orderStatus')->where('order_id',$order->id)
                ->orderBy('created_at','DESC')
                ->first();
            $tmp_arr['current_order_status'] = $current_status ? ($order->type == 'deliver' ? $current_status->orderStatus->translate('en-us')->delivery_description : $current_status->orderStatus->translate('en-us')->pickup_description) : null;
            $driver = $order->driver()->first();
            $tmp_arr['driver_code'] = $driver ? $driver->code : null;
            $tmp_arr['driver'] = $driver ? trim(ucwords($driver->first_name).' '.ucwords($driver->last_name)) : null;
            $order_data[] = $tmp_arr;
        }

        $filename = storage_path() . '/exports/orders_' . strtolower($concept->label) . '_' . Carbon::now()->format('Ymd_His') . '.csv';
        $writer = Writer::createFromPath($filename, 'w+');
        $writer->insertOne($columns);

        foreach ($order_data as $data) {
            $writer->insertOne($data);
        }
        $csv_url = $this->saveUploadedExport($filename);

        // add the new export model
        $export = new Export();
        $export->type = $type;
        $export->csv_uri = $csv_url;
        $export->concept_id = $concept->id;
        $export->save();

        try {
            unlink($filename);
        } catch (\Exception $e) {
            app('log')->error('Failed to delete the file: ' . $e->getMessage());
        }

        return $this->response->item($export, new ExportTransformer, ['key' => 'export']);
    }
}