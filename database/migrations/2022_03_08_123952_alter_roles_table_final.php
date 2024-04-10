<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRolesTableFinal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {

            //available units, maitinance, experts, expenses and owners

            $table->tinyInteger('buildings_management_none')->default(0);
            $table->tinyInteger('contracts_management_none')->default(0);
            $table->tinyInteger('payment_management_none')->default(0);
            $table->tinyInteger('tenant_management_none')->default(0);
            $table->tinyInteger('units_management_none')->default(0);


            $table->tinyInteger('avail_unit_create')->default(0);
            $table->tinyInteger('avail_unit_view')->default(0);
            $table->tinyInteger('avail_unit_edit')->default(0);
            $table->tinyInteger('avail_unit_delete')->default(0);
            $table->tinyInteger('avail_unit_none')->default(0);

            $table->tinyInteger('maintenance_req_create')->default(0);
            $table->tinyInteger('maintenance_req_view')->default(0);
            $table->tinyInteger('maintenance_req_edit')->default(0);
            $table->tinyInteger('maintenance_req_delete')->default(0);
            $table->tinyInteger('maintenance_req_none')->default(0);

            $table->tinyInteger('expert_create')->default(0);
            $table->tinyInteger('expert_view')->default(0);
            $table->tinyInteger('expert_edit')->default(0);
            $table->tinyInteger('expert_delete')->default(0);
            $table->tinyInteger('expert_none')->default(0);

            $table->tinyInteger('expense_create')->default(0);
            $table->tinyInteger('expense_view')->default(0);
            $table->tinyInteger('expense_edit')->default(0);
            $table->tinyInteger('expense_delete')->default(0);
            $table->tinyInteger('expense_none')->default(0);

            $table->tinyInteger('owner_create')->default(0);
            $table->tinyInteger('owner_view')->default(0);
            $table->tinyInteger('owner_edit')->default(0);
            $table->tinyInteger('owner_delete')->default(0);
            $table->tinyInteger('owner_none')->default(0);

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
