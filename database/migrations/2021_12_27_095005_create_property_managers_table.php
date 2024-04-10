<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_managers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('pm_company_id')->unsigned();
            $table->foreign('pm_company_id')->references('id')->on('property_manager_companies')->onDelete('cascade');
            $table->string('username');
            $table->integer('country_code')->default(0);
            $table->string('email')->unique();
            $table->string('email_verify_code');
            $table->string('office_contact_no' , 20);
            $table->unsignedTinyInteger('role_id');
            $table->unsignedInteger('country_id');
            $table->tinyInteger('status');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone',20)->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('property_managers');
    }
}
