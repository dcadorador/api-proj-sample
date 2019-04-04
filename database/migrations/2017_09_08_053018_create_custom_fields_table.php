<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('concept_id')->unsigned();
            $table->string('label', 255)->unique();
            $table->string('type', 255)->nullable();
            $table->timestamps();
         });
        DB::statement("ALTER TABLE custom_fields ADD UNIQUE no_duplicate_label_in_concept (concept_id, label)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_fields');
    }
}
