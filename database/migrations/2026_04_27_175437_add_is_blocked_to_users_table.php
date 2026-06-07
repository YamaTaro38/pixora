<?php
// database/migrations/2024_01_01_000011_add_is_blocked_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('is_active');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->text('block_reason')->nullable()->after('blocked_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'blocked_at', 'block_reason']);
        });
    }
};