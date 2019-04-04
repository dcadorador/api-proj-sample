<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('order_id')->unsigned()->nullable();
            $table->string('method')->nullable();
            $table->double('amount', 8, 2)->nullable();
            $table->double('cash_presented', 8,2)->nullable();
            $table->text('payment_reference_number')->nullable();
            $table->double('tip', 8, 2);
            $table->string('status')->nullable();
            $table->string('merchant_reference')->nullable();
            $table->string('last_4_digits')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
