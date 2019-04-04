<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('customer_address', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->string('status')->nullable();
            $table->string('label')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('telephone')->nullable();
            $table->text('instructions')->nullable();
            $table->string('photo_uri')->nullable();
            $table->integer('delivery_area_id')->unsigned()->nullable();
            $table->tinyInteger('enabled')->default(1)->nullable();
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
        Schema::dropIfExists('customer_address');
    }
}
