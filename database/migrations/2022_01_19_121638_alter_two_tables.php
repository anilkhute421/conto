<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTwoTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \DB::statement("ALTER TABLE `tenants_units` CHANGE `tenant_id` `tenant_id` BIGINT(4) NOT NULL;");
        \DB::statement("ALTER TABLE `tenants_units` CHANGE `rooms` `rooms` INT(4) NOT NULL;");
        \DB::statement("ALTER TABLE `tenants_units` CHANGE `bathrooms` `bathrooms` INT(4) NOT NULL;");


        \DB::statement("ALTER TABLE `avaliable_units` CHANGE `rooms` `rooms` INT(4) NOT NULL;");
        \DB::statement("ALTER TABLE `avaliable_units` CHANGE `bathrooms` `bathrooms` INT(4) NOT NULL;");


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
