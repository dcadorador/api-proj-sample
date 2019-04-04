<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('order_feedback')) {

            Schema::create('order_feedback', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->unsigned();
                $table->integer('rating')->nullable();
                $table->string('subject', 150)->nullable();
                $table->text('body')->nullable();
                $table->string('image', 255)->nullable();
                $table->integer('topic_id')->unsigned()->nullable();
                $table->timestamps();

                $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
        Schema::dropIfExists('order_feedback');
    }
}
