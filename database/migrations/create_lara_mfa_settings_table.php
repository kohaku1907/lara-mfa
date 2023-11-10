<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multi_factor_auth_settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('authenticatable', 'mfa_setting_type_auth_id_index');
            $table->text('recovery_codes')->nullable();
            $table->json('safe_devices')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('multi_factor_auth_settings');
    }
};
