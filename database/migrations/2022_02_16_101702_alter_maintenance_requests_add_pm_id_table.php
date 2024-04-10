<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMaintenanceRequestsAddPmIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    \DB::statement("ALTER TABLE `maintance_requests` ADD `property_manager_id` BIGINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `maintenance_request_id`;");

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
