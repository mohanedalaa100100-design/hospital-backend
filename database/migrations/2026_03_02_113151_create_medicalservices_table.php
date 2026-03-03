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
        Schema::create('medical_services', function (Blueprint $table) {
            $table->id();
            // الربط مع المستشفى عشان نعرف الخدمة دي تبع مين
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade');
            
            $table->string('name'); // اسم الخدمة (مثلاً: ICU Units أو Lab & Radiology)
            $table->string('icon_url')->nullable(); // عشان الأيقونات اللي في التصميم
            $table->text('description')->nullable(); // الوصف الصغير اللي تحت الاسم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_services');
    }
};
