<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference')->nullable();
            $table->string('code')->nullable();
            $table->timestamp('promised_time')->nullable();
            $table->string('source');
            $table->string('type');
            $table->double('subtotal', 6, 2);
            $table->double('total', 6, 2);
            $table->double('vat_amount', 6, 2);
            $table->double('discount', 6, 2);
            $table->double('delivery_charge', 6, 2);
            $table->string('coupon_code')->nullable();
            $table->text('notes')->nullable();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('customer_address_id')->unsigned()->nullable();
            $table->integer('device_id')->unsigned()->nullable();
            $table->integer('location_id')->unsigned()->nullable();
            $table->integer('concept_id')->unsigned()->nullable();
            $table->dateTime('scheduled_time')->nullable();
            $table->tinyInteger('customer_favorite')->default(0)->nullable();
            $table->string('payment_type')->nullable();
            $table->tinyInteger('is_posted')->default(0);
            $table->text('order_pos_response')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
