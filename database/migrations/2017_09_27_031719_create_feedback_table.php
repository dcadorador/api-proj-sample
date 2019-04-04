<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('feedbacks')) {
            Schema::create('feedbacks', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('concept_id');
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->string('subject')->nullable();
                $table->text('body')->nullable();
                $table->string('image_uri')->nullable();
                $table->integer('customer_id')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('feedbacks');
    }
}
