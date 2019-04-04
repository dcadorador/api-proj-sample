<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundledItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bundled_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_item_id')->unsigned();
            $table->integer('primary_item_id')->unsigned();
            $table->timestamps();

            $table->foreign('parent_item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('cascade');

            $table->foreign('primary_item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundled_items');
    }
}
