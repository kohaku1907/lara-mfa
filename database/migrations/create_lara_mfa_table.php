<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiFactorAuthenticationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multi_factor_authentications', function (Blueprint $table) {
            $table->id();
            $table->morphs('authenticatable', 'mfa_auth_type_auth_id_index');
            $table->string('channel'); // 'sms', 'email', 'totp'
            $table->text('secret')->nullable(); // For TOTP
            $table->text('recovery_codes')->nullable(); // For TOTP
            $table->timestampTz('enabled_at')->nullable();
            $table->unsignedTinyInteger('max_attempts')->default(5); // For SMS and email
            $table->timestampTz('last_attempt_at')->nullable(); // For SMS and email
            $table->unsignedTinyInteger('attempts')->default(0); // For SMS and email
            $table->json('safe_devices')->nullable();
            $table->timestampsTz();

            $table->unique(['authenticatable_type', 'authenticatable_id', 'channel'], 'mfa_auth_type_auth_id_channel_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('multi_factor_authentications');
    }
}
