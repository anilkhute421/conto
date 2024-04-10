<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPropertyManagersAddAddressFieldLatitudeFieldLongitudeFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_managers', function (Blueprint $table) {
            $table->string('address',255)->default("");
            $table->decimal('latitude', 10,8,['default'=>'0,0']);
            $table->decimal('longitude', 11,8,['default'=>'0,0']);


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
