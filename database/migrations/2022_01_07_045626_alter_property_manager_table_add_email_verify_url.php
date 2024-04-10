<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AlterPropertyManagerTableAddEmailVerifyUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_managers', function (Blueprint $table) {
            DB::statement("ALTER TABLE `property_managers` ADD `email_verify_url` VARCHAR(100) NOT NULL DEFAULT '' AFTER `email_verify_code`;");
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
