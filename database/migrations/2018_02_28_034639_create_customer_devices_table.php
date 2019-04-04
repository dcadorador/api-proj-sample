<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('customer_devices')) {
            Schema::create('customer_devices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('customer_id')->nullable();
                $table->integer('application_id')->nullable();
                $table->text('device_token')->nullable();
                $table->text('device_id')->nullable();
                $table->text('model')->nullable();
                $table->text('endpoint_arn')->nullable();
                $table->text('topic_subscription_arn')->nullable();
                $table->string('current_language')->nullable();
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
        Schema::dropIfExists('customer_devices');
    }
}
