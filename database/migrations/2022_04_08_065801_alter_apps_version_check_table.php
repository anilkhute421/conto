<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAppsVersionCheckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement("ALTER TABLE `mobile_app_versions` DROP `update_android`;");
        \DB::statement("ALTER TABLE `mobile_app_versions` DROP `update_ios`;");

        \DB::statement("ALTER TABLE `mobile_app_versions` ADD `update_android` VARCHAR(20) NULL DEFAULT NULL AFTER `ios`;");
        \DB::statement("ALTER TABLE `mobile_app_versions` ADD `update_ios` VARCHAR(20) NULL DEFAULT NULL AFTER `ios`;");

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
