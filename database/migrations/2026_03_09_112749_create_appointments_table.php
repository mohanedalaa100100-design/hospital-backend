<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // الربط مع الجداول التانية (Foreign Keys)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // صاحب الحساب
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade'); // الدكتور
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade'); // المستشفى
            
            // بيانات الموعد
            $table->date('appointment_date'); // يوم الحجز
            $table->time('appointment_time'); // ساعة الحجز
            
            // بيانات المريض (ممكن يحجز لنفسه أو لحد غيره)
            $table->string('patient_name');
            $table->string('patient_phone');
            
            // حالة الحجز
            $table->string('status')->default('pending'); // (pending, confirmed, cancelled)
            $table->text('notes')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};