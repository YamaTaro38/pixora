<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->decimal('down_payment', 12, 2)->nullable();
            $table->integer('duration_hours')->default(2);
            $table->integer('edited_photos')->default(10);
            $table->string('location_type')->default('studio'); // studio, outdoor, both
            $table->json('inclusions')->nullable(); // apa saja yang termasuk
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
};