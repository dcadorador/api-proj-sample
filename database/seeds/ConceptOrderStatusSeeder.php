<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Models\Concept;
use App\Api\V1\Models\OrderStatus;


class ConceptOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $concepts = Concept::whereNotIn('id',[2])->get();

        if($concepts) {
            foreach($concepts as $concept) {
                $max_group_id = app('db')->table('translations')->max('group_id');

                //*******************************23***************************************/

                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Initiated',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'بدأت',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Initiated',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'بدأت',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 23,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************24**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Waiting for Payment',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'في انتظار الدفع',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Waiting for Payment',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'في انتظار الدفع',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 24,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************25**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Sent to Branch',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'تم ارسال الطلب',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Sent to Branch',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'تم ارسال الطلب',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 25,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************26**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Order Received',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'تم استلام الطلب',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Order Received',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'تم استلام الطلب',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 26,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************27**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'In Kitchen',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'في المطبخ',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'In Kitchen',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'في المطبخ',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 27,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************28**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Ready For Delivery',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'جاهز لتوصيل',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Ready For Collection',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'جاهز للتجميع',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 28,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************29**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Dispatched',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'أرسل',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Dispatched',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'أرسل',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 29,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************30**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Out for Delivery',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'خارج للتوصيل',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Out for Delivery',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'خارج للتوصيل',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 30,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************31**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Arrived at Destination',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'وصلت إلى الوجهة',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Arrived at Destination',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'وصلت إلى الوجهة',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 31,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************32**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Delivered',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'تم التوصيل',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Collected',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'تم جمع الطلب',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 32,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************33**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Cancelled by Customer',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'ألغى من قبل العميل',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Cancelled by Customer',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'ألغى من قبل العميل',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 33,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************34**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Cancelled',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'ملغى',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Cancelled',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'ملغى',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 34,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);

                //********************************35**************************************/
                $max_group_id = app('db')->table('translations')->max('group_id');
                // PICKUP
                $grp_id1 = $max_group_id + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'Order failed',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id1,
                    'value' => 'فشل الطلب',
                    'locale' => 'ar-sa'
                ]);

                // DELIVERY
                $grp_id2 = $grp_id1 + 1;
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'Order failed',
                    'locale' => 'en-us'
                ]);
                app('db')->table('translations')->insert([
                    'group_id' =>  $grp_id2,
                    'value' => 'فشل الطلب',
                    'locale' => 'ar-sa'
                ]);

                app('db')->table('concept_order_statuses')->insert([
                    'concept_id' =>  $concept->id,
                    'order_status_id' => 35,
                    'delivery_description' => $grp_id1,
                    'pickup_description' => $grp_id2
                ]);
            }
        }

    }
}