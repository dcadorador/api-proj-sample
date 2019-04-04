<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('employee_bearings')) {
            Schema::create('employee_bearings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('employee_id')->unsigned()->nullable();
                $table->double('lat', 16, 12)->nullable();
                $table->double('long', 16, 12)->nullable();
                $table->timestamps();

                $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('employee_bearings');
    }
}
