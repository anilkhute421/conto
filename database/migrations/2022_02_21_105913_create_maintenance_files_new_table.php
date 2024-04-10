<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceFilesNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maintenance_request_id')->unsigned();
            $table->tinyInteger('file_type')->unsigned(); // 1-image , 2-video
            $table->string('file_name',200);
            $table->string('thumbnail_name',200);
            $table->bigInteger('uploded_by')->unsigned();
            $table->foreign('maintenance_request_id')->references('id')->on('maintance_requests')->onDelete('cascade');
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
        Schema::dropIfExists('maintenance_files_new');
    }
}
