<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterExpnesesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //\DB::statement("")
        \DB::statement("ALTER TABLE `expenses` DROP `expense_item_id`;");
        \DB::statement("ALTER TABLE `expenses` DROP `currency_id`;");
        \DB::statement("ALTER TABLE `expenses` DROP `cost`;");
        \DB::statement("ALTER TABLE `expenses` DROP `date`;");

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
