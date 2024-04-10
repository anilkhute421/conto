<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PropertyManagerLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_manager_logs', function (Blueprint $table) {

            $table->id();

            $table->string('module',30); //building, avail units, tenant unit, .....

            $table->string('action',30);//Action: create/edit/delete /status change

            $table->bigInteger('affected_record_id')->unsigned();//building id, avail units id, tenant unit id, .....

            $table->bigInteger('pm_company_id')->unsigned();
            $table->bigInteger('property_manager_id')->unsigned();

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
        Schema::dropIfExists('property_manager_logs');

    }
}
