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
        Schema::create('quick_actions', function (Blueprint $table) {
            $table->id();
            $table->string('title');        // مثلاً: Emergency Mode أو Normal Mode
            $table->text('description');    // النص الصغير اللي تحت العنوان في Figma
            $table->string('image_url');    // رابط الصورة الخلفية (الحمراء أو الزرقاء)
            $table->string('type');         // نوع الزرار عشان البرمجة (emergency أو normal)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quick_actions');
    }
};