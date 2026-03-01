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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // اسم المستشفى
            $table->string('address');        // العنوان النصي
            
            // --- الإضافات الجديدة لدعم التصميم وميزة الطوارئ ---
            $table->string('image_url')->nullable();      // رابط صورة المستشفى
            $table->decimal('lat', 10, 8)->nullable();    // خط العرض (Latitude)
            $table->decimal('lng', 11, 8)->nullable();    // خط الطول (Longitude)
            // ------------------------------------------------

            $table->boolean('is_featured')->default(false); // للمستشفيات المميزة في الصفحة الرئيسية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};