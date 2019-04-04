<?php

use App\Api\V1\Models\Order;
use App\Api\V1\Models\OrderStatus;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        /*Order::create([
            'order_number' => '123456',
            'promised_time' => new DateTime,
            'location_id' => 1,
            'source' => 'iphone',
            'type' => 'eat-in'
        ]);

        DB::table('item_order')->insert([
            'item_id' => 1,
            'order_id' => 1,
            'quantity' => 2,
            'price' => 20.00
        ]);

        DB::table('item_modifier')->insert([
            'item_id' => 1,
            'modifier_id' => 1,
            'quantity' => 1,
            'price' => 1.00
        ]);*/

        $description = [
            'en-us' => 'Initiated',
            'ar-sa' => 'Initiated'
        ];

        OrderStatus::create([
            'sequence' => 0,
            'code' => 'initiated',
            'type' => 'unconfirmed',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $description = [
            'en-us' => 'Waiting for Payment',
            'ar-sa' => 'Waiting for Payment'
        ];

        OrderStatus::create([
            'sequence' => 1,
            'code' => 'waiting-for-payment',
            'type' => 'unconfirmed',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $description = [
            'en-us' => 'Posted',
            'ar-sa' => 'Posted'
        ];

        OrderStatus::create([
            'sequence' => 2,
            'code' => 'posted',
            'type' => 'open',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $description = [
            'en-us' => 'Accepted',
            'ar-sa' => 'قبلت'
        ];

        OrderStatus::create([
            'sequence' => 3,
            'code' => 'accepted',
            'type' => 'open',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $description = [
            'en-us' => 'In Kitchen',
            'ar-sa' => 'في المطبخ'
        ];

        OrderStatus::create([
            'sequence' => 4,
            'code' => 'in-kitchen',
            'type' => 'open',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $delivery_description = [
            'en-us' => 'Ready For Delivery',
            'ar-sa' => 'مستعد لتوصيل'
        ];

        $pickup_description = [
            'en-us' => 'Ready For Collection',
            'ar-sa' => 'جاهزة للتجميع'
        ];

        OrderStatus::create([
            'sequence' => 5,
            'code' => 'ready',
            'type' => 'open',
            'delivery_description' => $delivery_description,
            'pickup_description' => $pickup_description
        ]);

        $description = [
            'en-us' => 'Dispatched',
            'ar-sa' => 'أرسل'
        ];

        OrderStatus::create([
            'sequence' => 6,
            'code' => 'dispatched',
            'type' => 'open',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $description = [
            'en-us' => 'Out for Delivery',
            'ar-sa' => 'خارج للتوصيل'
        ];

        OrderStatus::create([
            'sequence' => 7,
            'code' => 'delivery-in-progress',
            'type' => 'open',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $description = [
            'en-us' => 'Arrived at Destination',
            'ar-sa' => 'وصلت إلى الوجهة'
        ];

        OrderStatus::create([
            'sequence' => 8,
            'code' => 'arrived-at-customer',
            'type' => 'open',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $delivery_description = [
            'en-us' => 'Delivered',
            'ar-sa' => 'تم التوصيل'
        ];

        $pickup_description = [
            'en-us' => 'Collected',
            'ar-sa' => 'جمع'
        ];

        OrderStatus::create([
            'sequence' => 9,
            'code' => 'delivered',
            'type' => 'closed',
            'delivery_description' => $delivery_description,
            'pickup_description' => $pickup_description
        ]);

        $description = [
            'en-us' => 'Cancelled by Customer',
            'ar-sa' => 'ألغى العميل'
        ];

        OrderStatus::create([
            'sequence' => 10,
            'code' => 'cancelled-by-customer',
            'type' => 'closed',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);

        $description = [
            'en-us' => 'Cancelled',
            'ar-sa' => 'ألغى العميل'
        ];

        OrderStatus::create([
            'sequence' => 11,
            'code' => 'cancelled-by-employee',
            'type' => 'closed',
            'delivery_description' => $description,
            'pickup_description' => $description
        ]);


        /*DB::table('order_order_status')->insert([
            'order_id' => 1,
            'order_status_id' => 1,
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ]);

        sleep(1);

        DB::table('order_order_status')->insert([
            'order_id' => 1,
            'order_status_id' => 2,
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ]);*/

    }
}