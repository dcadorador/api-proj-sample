<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFavoritesModifiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('favorite_item_modifiers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('favorite_item_id')->unsigned()->nullable();
            $table->integer('modifier_id')->unsigned()->nullable();
            $table->integer('quantity')->unsigned()->nullable();
            $table->double('price', 6, 2);

            $table->foreign('favorite_item_id')
                ->references('id')
                ->on('customer_favorites')
                ->onDelete('cascade');

            $table->foreign('modifier_id')
                ->references('id')
                ->on('modifiers')
                ->onDelete('cascade');

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
        //
        Schema::dropIfExists('favorites_modifiers');
    }
}
