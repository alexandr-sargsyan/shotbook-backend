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
        Schema::create('video_reference_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('video_reference_id')->constrained('video_references')->onDelete('cascade');
            $table->timestamps();

            // Уникальный индекс: один пользователь может лайкнуть видео только один раз
            $table->unique(['user_id', 'video_reference_id']);
            $table->index('user_id');
            $table->index('video_reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_reference_likes');
    }
};
