<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants_units', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pm_company_id');
            $table->bigInteger('building_id');
            $table->bigInteger('owner_id');
            $table->Integer('unit_no');
            $table->string('unit_code',50);
            $table->tinyInteger('rooms');
            $table->string('address',500);
            $table->tinyInteger('bathrooms');
            $table->Integer('area_sqm');
            $table->Integer('currency_id');
            $table->Integer('monthly_rent');
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
        Schema::dropIfExists('tenants_units');
    }
}
