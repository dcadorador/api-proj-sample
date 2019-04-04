<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemModifierTable extends Migration
{
    /**
     * Table to hold required modifiers when an item is added to an order.
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_order_modifier', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('modifier_id')->unsigned();
            $table->integer('item_order_id')->unsigned();
            $table->integer('quantity');
            $table->double('price', 6, 2);
            $table->timestamps();

            $table->foreign('modifier_id')
                  ->references('id')
                  ->on('modifiers')
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
        Schema::dropIfExists('item_modifier');
    }
}
