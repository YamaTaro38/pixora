<?php
// database/migrations/2024_01_01_000001_create_bookings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Hapus tabel jika ada
        Schema::dropIfExists('bookings');
        
        // Buat tabel baru dengan semua kolom
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('booking_code')->unique();
            $table->string('public_token')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->foreignId('package_id')->constrained()->onDelete('restrict');
            $table->date('booking_date');
            $table->string('time_slot');
            $table->decimal('total_price', 12, 2);
            $table->decimal('down_payment', 12, 2)->nullable();
            $table->enum('payment_status', ['pending', 'dp_paid', 'lunas', 'expired', 'cancelled'])->default('pending');
            $table->enum('session_status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            $table->enum('booking_status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->string('payment_method')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('slot_locked_until')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();
            
            $table->index('public_token');
            $table->index('booking_code');
            $table->index('customer_phone');
            $table->index(['booking_date', 'time_slot']);
            $table->index('payment_status');
            $table->index('booking_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};