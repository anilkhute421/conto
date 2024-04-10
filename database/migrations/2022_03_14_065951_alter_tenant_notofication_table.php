<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTenantNotoficationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        \DB::statement("ALTER TABLE `tenant_notifications` ADD `property_manager_id` BIGINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `tenant_id`;");
        \DB::statement("ALTER TABLE `tenant_notifications` ADD `pm_company_id` BIGINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `tenant_id`;");

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
