<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('concept_id')->unsigned()->nullable();
            $table->string('code')->nullable();
            $table->integer('name')->unsigned()->nullable();
            $table->string('image_uri')->nullable();
            $table->integer('display_order')->default(0);
            $table->double('price', 6, 2);
            $table->integer('calorie_count')->default(0);
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
        Schema::dropIfExists('ingredients');
    }
}
