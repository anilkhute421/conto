<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
           
            \DB::statement("ALTER TABLE `tenants` CHANGE `password` `password` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
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
