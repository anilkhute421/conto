<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPropertyManagerCompaniesRemoveUniqueEmailOrPhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_manager_companies', function (Blueprint $table) {
            $table->dropUnique('property_manager_companies_email_unique');
            $table->dropUnique('property_manager_companies_phone_unique');
     
          
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
