<?php
// database/migrations/2024_01_01_000007_add_payment_expiry_to_bookings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('payment_expiry')->nullable()->after('midtrans_response');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_expiry');
        });
    }
};