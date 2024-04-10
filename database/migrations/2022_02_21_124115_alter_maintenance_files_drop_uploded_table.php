<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMaintenanceFilesDropUplodedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement(" ALTER TABLE `maintenance_files` ADD `upload_by` BIGINT NOT NULL AFTER `updated_at`;");

        \DB::statement("ALTER TABLE `maintenance_files` DROP `uploded_by`;");
       

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
