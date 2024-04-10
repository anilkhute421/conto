<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivacyTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('privacy_terms', function (Blueprint $table) {
            $table->id();
            $table->longText('pm_privacy_policy_en')->nullable();
            $table->longText('pm_privacy_policy_ar')->nullable();
            $table->longText('pm_terms_conditions_en')->nullable();
            $table->longText('pm_terms_conditions_ar')->nullable();
            $table->longText('app_privacy_policy_en')->nullable();
            $table->longText('app_privacy_policy_ar')->nullable();
            $table->longText('app_terms_conditions_en')->nullable();
            $table->longText('app_terms_conditions_ar')->nullable();
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
        Schema::dropIfExists('privacy_terms');
    }
}
