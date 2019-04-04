<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('menu_id')->unsigned()->nullable();
            $table->integer('display_order');
            // $table->string('name');
            $table->integer('name')->unsigned()->nullable();
            // $table->string('description');
            $table->integer('description')->unsigned()->nullable();
            $table->string('image_uri')->nullable();
            $table->tinyInteger('enabled')->default(1);
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
        Schema::dropIfExists('categories');
    }
}
