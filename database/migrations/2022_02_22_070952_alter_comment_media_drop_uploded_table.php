<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCommentMediaDropUplodedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement(" ALTER TABLE `comment_media` ADD `upload_by` BIGINT NOT NULL AFTER `updated_at`;");

        \DB::statement("ALTER TABLE `comment_media` DROP `uploded_by`;");
       
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
