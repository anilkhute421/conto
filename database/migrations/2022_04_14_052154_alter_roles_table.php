<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        \DB::statement("ALTER TABLE `roles` ADD `amount_view` TINYINT NOT NULL DEFAULT 0 AFTER `owner_none`;");
        \DB::statement("ALTER TABLE `roles` ADD `amount_none` TINYINT NOT NULL DEFAULT 0 AFTER `owner_none`;");



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
