<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            // إضافة البيانات اللي في تصميم الـ Details
            $table->string('rating')->default('4.2'); // التقييم اللي جنب النجمة
            $table->string('accreditation')->default('JCI Accredited'); // الاعتماد الدولي
            $table->string('whatsapp')->nullable(); // رقم الواتساب الأحمر
            $table->string('working_hours')->default('Available 24/7'); // مواعيد العمل
            $table->text('about')->nullable(); // رسالة الترحيب "WELCOME TO..."
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn(['rating', 'accreditation', 'whatsapp', 'working_hours', 'about']);
        });
    }
};