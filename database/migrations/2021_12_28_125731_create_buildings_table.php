<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pm_company_id');
            $table->bigInteger('property_manager_id');
            $table->string('building_name',50);
            $table->string('building_code',50);
            $table->string('address',500);
            $table->Integer('units')->default(0);
            $table->string('location_link',500);
            $table->string('description',500);
            $table->tinyInteger('status');
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
        Schema::dropIfExists('buildings');
    }
}
