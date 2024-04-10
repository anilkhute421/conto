<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTenantsUnitsTableFinal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('tenants_units', function (Blueprint $table) {

            $table->bigInteger('pm_company_id');
            $table->bigInteger('building_id')->unsigned();
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');

            $table->bigInteger('owner_id');

            $table->Integer('unit_no');
            $table->string('unit_code',50);
            $table->tinyInteger('rooms');
            $table->string('address',500);
            $table->tinyInteger('bathrooms');
            $table->Integer('area_sqm');
            $table->Integer('monthly_rent');
            $table->string('description',500);
            $table->tinyInteger('status');
        });
        \DB::statement("ALTER TABLE `tenants_units` DROP `unit_id`;");


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
