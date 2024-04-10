<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTenetsAddUniqueEmailKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `tenants` ADD `unique_email_key` VARCHAR(50) NOT NULL DEFAULT '' AFTER `email`, ADD `email_key_expire` VARCHAR(50) NOT NULL DEFAULT '' AFTER `unique_email_key`;");
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
