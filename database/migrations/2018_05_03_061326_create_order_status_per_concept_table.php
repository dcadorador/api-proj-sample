<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderStatusPerConceptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('concept_order_statuses',function(Blueprint $table){
            $table->increments('id');
            $table->integer('concept_id')->unsigned()->nullable();
            $table->integer('order_status_id')->unsigned()->nullable();
            $table->integer('delivery_description')->unsigned()->nullable();
            $table->integer('pickup_description')->unsigned()->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('concept_order_statuses');
    }
}
