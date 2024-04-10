<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterExpenseItemDeleteFilesNameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement("ALTER TABLE `expenses_items` DROP `file_name`;");
        \DB::statement("ALTER TABLE `expenses_items` DROP `expense_item_id`;");
        \DB::statement("ALTER TABLE `expenses_items` ADD `expenses_lines_id`  BIGINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `updated_at`;");



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
