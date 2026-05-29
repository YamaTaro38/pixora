<?php
// database/migrations/2024_01_01_000017_alter_users_table_for_google_tokens.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ubah google_token dari varchar(255) menjadi text
            $table->text('google_token')->nullable()->change();
            // Juga ubah google_refresh_token jika ada
            $table->text('google_refresh_token')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_token', 255)->nullable()->change();
            $table->string('google_refresh_token', 255)->nullable()->change();
        });
    }
};