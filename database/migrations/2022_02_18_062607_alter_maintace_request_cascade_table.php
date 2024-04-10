<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMaintaceRequestCascadeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        //     Schema::table('avaliable_units', function (Blueprint $table) {
        //         \DB::statement("ALTER TABLE `avaliable_units` DROP `building_id`;");
        //         $table->bigInteger('building_id')->unsigned();
        //         $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
        //    });

        Schema::table('expense_files', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `expense_files` DROP `expense_item_id`;");
            $table->bigInteger('expense_item_id')->unsigned();
            $table->foreign('expense_item_id')->references('id')->on('expenses_items')->onDelete('cascade');
        });
        
        Schema::table('expenses_items', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `expenses_items` DROP `expenses_id`;");
            $table->bigInteger('expenses_id')->unsigned();
            $table->foreign('expenses_id')->references('id')->on('expenses')->onDelete('cascade');
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
