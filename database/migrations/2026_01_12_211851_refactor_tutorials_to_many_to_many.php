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
        // Создаем pivot таблицу для связи многие-ко-многим
        Schema::create('tutorial_video_reference', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutorial_id')->constrained('tutorials')->onDelete('cascade');
            $table->foreignId('video_reference_id')->constrained('video_references')->onDelete('cascade');
            $table->integer('start_sec')->nullable();
            $table->integer('end_sec')->nullable();
            $table->timestamps();

            // Уникальный индекс: один tutorial может быть связан с одним video_reference только один раз
            $table->unique(['tutorial_id', 'video_reference_id']);
        });

        // Удаляем старые поля из таблицы tutorials
        Schema::table('tutorials', function (Blueprint $table) {
            $table->dropForeign(['video_reference_id']);
            $table->dropColumn(['video_reference_id', 'start_sec', 'end_sec']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем старые поля в tutorials
        Schema::table('tutorials', function (Blueprint $table) {
            $table->foreignId('video_reference_id')->nullable()->constrained('video_references')->onDelete('cascade');
            $table->integer('start_sec')->nullable();
            $table->integer('end_sec')->nullable();
        });

        // Удаляем pivot таблицу
        Schema::dropIfExists('tutorial_video_reference');
    }
};
