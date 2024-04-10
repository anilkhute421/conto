<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMaintenanceRequestTableAddTenantUnreadDateOnlyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement("ALTER TABLE `maintance_requests` DROP `tenant_unread_date`;");
        \DB::statement("ALTER TABLE `maintance_requests` ADD `tenant_unread_date` DATE NULL DEFAULT NULL AFTER `tenant_unread_count`;");



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
