<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPropertyManagersLogsAddNewColoumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement("ALTER TABLE `property_manager_logs` DROP `deleted_record_name`;");

        \DB::statement("ALTER TABLE `property_manager_logs` ADD `record_name` VARCHAR(30) NOT NULL DEFAULT '' AFTER `affected_record_id`;");

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
