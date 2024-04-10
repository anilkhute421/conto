<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailableUnitImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('available_unit_image', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('unit_id')->unsigned();
            $table->foreign('unit_id')->references('id')->on('avaliable_units')->onDelete('cascade');
            $table->string('image_name',255);
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
        Schema::dropIfExists('available_unit_image');
    }
}
