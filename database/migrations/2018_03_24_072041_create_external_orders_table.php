<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('external_orders')) {
            Schema::create('external_orders', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('concept_id')->unsigned()->nullable();
                $table->integer('location_id')->unsigned()->nullable();
                $table->string('order_hid')->nullable();
                $table->string('reference')->nullable();
                $table->dateTime('order_time')->nullable();
                $table->dateTime('promised_time')->nullable();
                $table->decimal('total', 6, 2)->nullable();
                $table->string('customer_hid')->nullable();
                $table->string('customer')->nullable();
                $table->string('customer_phone')->nullable();
                $table->string('delivery_hid')->nullable();
                $table->text('delivery_address')->nullable();
                $table->double('delivery_address_longitude', 16, 12)->nullable();
                $table->double('delivery_address_latitude', 16, 12)->nullable();
                $table->string('payment_hid')->nullable();
                $table->string('payment_amount')->nullable();
                $table->string('payment_method')->nullable();
                $table->dateTime('payment_date')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('external_orders');
    }
}
