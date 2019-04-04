<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->uuid('concept_id');
            $table->string('status');
            $table->integer('name')->unsigned()->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('country');
            $table->double('lat', 16, 12);
            $table->double('long', 16, 12);
            $table->string('pos');
            $table->double('delivery_charge', 5, 2)->default(0.0);
            $table->string('opening_hours', 2048);
            $table->tinyInteger('delivery_enabled');
            $table->integer('promised_time_delta_delivery')->default(45);
            $table->integer('promised_time_delta_pickup')->default(20);
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
        Schema::dropIfExists('locations');
    }
}
