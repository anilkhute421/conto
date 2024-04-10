<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants_temp', function (Blueprint $table) {
            $table->id();
            $table->string('first_name',50);
            $table->string('last_name',50);
            $table->bigInteger('property_manager_id')->unsigned();
            $table->bigInteger('building_id')->unsigned();
            $table->string('email',150);
            $table->bigInteger('unit_id')->unsigned();
            $table->string('phone',20);
            $table->integer('country_code')->default(0);
            $table->unsignedInteger('country_id');
            $table->string('language',2);
            $table->string('password',50);
            $table->string('address',255);
            $table->tinyInteger('os_type');
            $table->string('os_version',10);
            $table->string('device_token',255);
            $table->string('app_version',10);
            $table->string('otp',10);
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('tenants_temp');
    }
}
