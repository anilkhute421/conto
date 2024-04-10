<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('payments__tables');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pm_company_id')->unsigned();
            $table->bigInteger('tenant_id')->unsigned();
            $table->bigInteger('building_id')->unsigned();
            $table->bigInteger('unit_id')->unsigned();
            $table->tinyInteger('payment_type');
            $table->string('cheque_no',100)->default("");
            $table->date('payment_date')->nullable();
            $table->integer('amount')->default(0);
            $table->tinyInteger('status');
            $table->string('remark',500);
            $table->string('payment_code',50);
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
        //
    }
}
