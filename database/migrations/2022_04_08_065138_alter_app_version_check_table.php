<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAppVersionCheckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement("ALTER TABLE `mobile_app_versions` ADD `update_android` INT NULL DEFAULT NULL AFTER `ios`;");
        \DB::statement("ALTER TABLE `mobile_app_versions` ADD `update_ios` INT NULL DEFAULT NULL AFTER `ios`;");

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
