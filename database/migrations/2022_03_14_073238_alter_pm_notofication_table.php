<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPmNotoficationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement("ALTER TABLE `pm_notifications` DROP `message`;");
        \DB::statement("ALTER TABLE `pm_notifications` ADD `message` VARCHAR(500) NOT NULL DEFAULT '' AFTER `title`;");


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
