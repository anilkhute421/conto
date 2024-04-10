<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title',50);
            $table->string('message',50);
            $table->bigInteger('property_manager_id')->unsigned();
            $table->string('message_language',2);
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
        Schema::dropIfExists('pm_notifications');
    }
}
