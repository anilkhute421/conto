<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->bigInteger('pm_company_id')->unsigned();
            $table->bigInteger('Tenant_id')->unsigned();
            $table->bigInteger('building_id')->unsigned();
            $table->bigInteger('unit_id')->unsigned();
            $table->string('start_date');
            $table->string('end_date');
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
        Schema::dropIfExists('contracts_tables');
    }
}
