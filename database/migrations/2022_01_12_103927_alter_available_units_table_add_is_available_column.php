<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAvailableUnitsTableAddIsAvailableColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avaliable_units', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `avaliable_units` ADD `is_available` TINYINT(1) NOT NULL DEFAULT '1' AFTER `status`;");
        });
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
