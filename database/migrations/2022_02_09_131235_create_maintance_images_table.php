<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintanceImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintance_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maintenance_id')->unsigned();
            $table->foreign('maintenance_id')->references('id')->on('maintance_requests')->onDelete('cascade');
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
        Schema::dropIfExists('maintance_images');
    }
}
