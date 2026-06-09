<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('method');
            $table->string('transaction_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('va_number')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('payment_code')->nullable();
            $table->text('qr_url')->nullable();
            $table->text('qr_string')->nullable();
            $table->text('deeplink')->nullable();
            $table->text('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
