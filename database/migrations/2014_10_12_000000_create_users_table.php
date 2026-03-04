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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            
            // التعديل: شيلنا الـ nullable() عشان نضمن إن كل يوزر ليه رقم تليفون للطوارئ والـ OTP
            $table->string('phone')->unique(); 
            
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // --- نظام الصلاحيات ---
            $table->boolean('is_admin')->default(false); 
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};