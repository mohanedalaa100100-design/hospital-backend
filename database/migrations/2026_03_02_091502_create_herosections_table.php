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
        Schema::create('herosections', function (Blueprint $table) {
            $table->id();
            $table->string('title');        // العنوان: Emergency medical assistance...
            $table->text('description');    // النص: Connecting you to life-saving care...
            $table->string('image_url');    // رابط صورة عربيات الإسعاف
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('herosections');
    }
};