<?php
// database/migrations/2024_01_01_000004_create_pose_references_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pose_references', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // wedding, prewedding, family, portrait, maternity, etc
            $table->string('title');
            $table->string('image_url');
            $table->string('thumbnail_url')->nullable();
            $table->text('description')->nullable();
            $table->string('source')->default('local'); // local, ai_generated
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pose_references');
    }
};