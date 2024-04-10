<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAvailTenantUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \DB::statement("ALTER TABLE `avaliable_units` CHANGE `unit_no` `unit_no` VARCHAR(50) NULL DEFAULT '';");

        \DB::statement("ALTER TABLE `tenants_units` CHANGE `unit_no` `unit_no` VARCHAR(50) NULL DEFAULT '';");
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
