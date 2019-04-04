<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConceptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('concepts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->string('label');
            $table->string('country');
            $table->string('dialing_code');
            $table->string('currency_code');
            $table->string('currency_symbol');
            $table->string('logo_uri')->nullable();
            $table->string('default_opening_hours')->nullable();
            $table->integer('default_menu_id')->unsigned()->nullable();
            $table->string('default_pos')->default('');
            $table->string('feedback_email')->nullable();
            $table->double('default_delivery_charge', 5, 2)->default(0.0);
            $table->integer('default_promised_time_delta_delivery')->default(45);
            $table->integer('default_promised_time_delta_pickup')->default(45);
            $table->integer('default_schedule_delivery_time')->nullable();
            $table->integer('order_cancellation_time')->nullable();
            $table->string('order_cancellation_max_status')->nullable();
            $table->string('vat_type')->nullable();
            $table->integer('vat_rate')->nullable();
            $table->integer('default_minimum_order_amount')->unsigned()->nullable();
            $table->integer('default_driver_location_ttl')->unsigned()->nullable();
            $table->string('default_order_status_cash')->nullable();
            $table->string('default_order_status_card')->nullable();
            $table->integer('minimum_order_amount_delivery')->nullable();
            $table->integer('minimum_order_amount_pickup')->nullable();
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
        Schema::dropIfExists('concepts');
    }
}
