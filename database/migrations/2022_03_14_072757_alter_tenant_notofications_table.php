<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTenantNotoficationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement("ALTER TABLE `tenant_notifications` DROP `message`;");
        \DB::statement("ALTER TABLE `tenant_notifications` ADD `message` VARCHAR(500) NOT NULL DEFAULT '' AFTER `title`;");


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
