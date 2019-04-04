<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDeliveryAreas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('delivery_areas', function(Blueprint $table) {
            //$table->string('label')->nullable()->change();
            $table->integer('name')->unsigned()->nullable();
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
        Schema::table('delivery_areas', function(Blueprint $table) {
            //$table->string('label')->nullable()->change();
            $table->integer('name')->unsigned()->nullable();
        });
    }
}
