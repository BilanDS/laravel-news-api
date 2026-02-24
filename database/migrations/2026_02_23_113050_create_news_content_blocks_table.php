<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_content_blocks', function (Blueprint $table) {
            $table->id();
            // Зв'язок з конкретною новиною
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();

            // Тип блоку (text, image, text_image_right, text_image_left)
            $table->string('type');

            // Вміст (може бути пустим, якщо це тільки картинка)
            $table->text('text_content')->nullable();

            // Шлях до картинки (може бути пустим, якщо це тільки текст)
            $table->string('image_path')->nullable();

            // Порядок відображення блоку
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_content_blocks');
    }
};
