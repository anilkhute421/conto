<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintance_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pm_company_id')->unsigned();
            $table->bigInteger('tenant_id')->unsigned();
            $table->bigInteger('building_id')->unsigned();
            $table->bigInteger('unit_id')->unsigned();
            $table->bigInteger('expert_id')->unsigned();
            $table->tinyInteger('status');
            $table->string('description',500);
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
        Schema::dropIfExists('maintance_requests');
    }
}
