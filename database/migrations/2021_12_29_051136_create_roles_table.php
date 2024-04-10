<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            
            $table->id();

            $table->string('role_title',20);

            $table->tinyInteger('buildings_management_create');
            $table->tinyInteger('buildings_management_view');
            $table->tinyInteger('buildings_management_edit');
            $table->tinyInteger('buildings_management_delete');

            $table->tinyInteger('contracts_management_create');
            $table->tinyInteger('contracts_management_view');
            $table->tinyInteger('contracts_management_edit');
            $table->tinyInteger('contracts_management_delete');

            $table->tinyInteger('payment_management_create');
            $table->tinyInteger('payment_management_view');
            $table->tinyInteger('payment_management_edit');
            $table->tinyInteger('payment_management_delete');

            $table->tinyInteger('tenant_management_create');
            $table->tinyInteger('tenant_management_view');
            $table->tinyInteger('tenant_management_edit');
            $table->tinyInteger('tenant_management_delete');

            $table->tinyInteger('units_management_create');
            $table->tinyInteger('units_management_view');
            $table->tinyInteger('units_management_edit');
            $table->tinyInteger('units_management_delete');
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
        Schema::dropIfExists('roles');
    }
}
