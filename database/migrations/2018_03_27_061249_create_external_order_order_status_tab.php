<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalOrderOrderStatusTab extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('external_order_order_status')) {
            Schema::create('external_order_order_status', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('external_order_id')->unsigned()->nullable();
                $table->integer('external_order_status_id')->unsigned()->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
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
        Schema::dropIfExists('external_order_order_status');
    }
}
