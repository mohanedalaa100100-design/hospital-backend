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
            $table->string('phone')->unique()->nullable(); // ضفنا التليفون عشان الطوارئ
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // --- الخانة السحرية للأدمن ---
            $table->boolean('is_admin')->default(false); 
            // false (0) = مستخدم عادي
            // true (1) = مدير نظام (أدمن)
            
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