<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('expenses_id')->unsigned();
            $table->bigInteger('expense_item_id')->unsigned();
            $table->bigInteger('currency_id')->unsigned();
            $table->integer('cost')->default(0);
            $table->date('date');
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
        Schema::dropIfExists('expenses_items');
    }
}
