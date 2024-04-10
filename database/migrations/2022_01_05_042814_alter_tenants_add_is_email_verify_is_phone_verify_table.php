<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTenantsAddIsEmailVerifyIsPhoneVerifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
        DB::statement("ALTER TABLE `tenants` ADD `is_email_verify` TINYINT NOT NULL DEFAULT '0' AFTER `status`, ADD `is_phone_verify` TINYINT NOT NULL DEFAULT '0' AFTER `is_email_verify`;
        ");
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
