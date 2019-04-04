<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalOrdersStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('external_order_statuses')) {
            Schema::create('external_order_statuses', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sequence');
                $table->string('type');
                $table->string('code');
                $table->integer('delivery_description')->unsigned()->nullable();
                $table->integer('pickup_description')->unsigned()->nullable();
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
        Schema::dropIfExists('external_order_statuses');
    }
}
