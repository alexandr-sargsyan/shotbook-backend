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
        Schema::create('video_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('video_collections')->onDelete('cascade');
            $table->foreignId('video_reference_id')->constrained('video_references')->onDelete('cascade');
            $table->timestamps();

            // Уникальный индекс: одно видео можно добавить в коллекцию только один раз
            $table->unique(['collection_id', 'video_reference_id']);
            $table->index('collection_id');
            $table->index('video_reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_collection_items');
    }
};
