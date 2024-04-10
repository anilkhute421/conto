<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPropertyManagersAddAddressFieldLatitudeFieldLongitudeFieldForLengthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_managers', function (Blueprint $table) {
            // DB::statement("ALTER TABLE `property_managers` CHANGE `latitude` `latitude` DECIMAL(52,8) UNSIGNED NOT NULL, CHANGE `longitude` `longitude` DECIMAL(52,8) UNSIGNED NOT NULL;
            // ");

            DB::statement("ALTER TABLE `property_managers` CHANGE `address` `address` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';
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
        
    }
}
