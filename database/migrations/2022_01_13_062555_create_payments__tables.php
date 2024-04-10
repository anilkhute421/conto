<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments__tables', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pm_company_id')->unsigned();
            $table->bigInteger('tenant_id')->unsigned();
            $table->bigInteger('building_id')->unsigned();
            $table->bigInteger('unit_id')->unsigned();
            $table->tinyInteger('payment_type');
            $table->integer('cheque_amount');
            $table->string('cheque_no',50);
            $table->string('cheque_date');
            $table->tinyInteger('cheque_status');
            $table->integer('manp_amount');
            $table->string('manp_date');
            $table->tinyInteger('manp_status');
            $table->string('remark',255);
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
        Schema::dropIfExists('payments__tables');
    }
}
