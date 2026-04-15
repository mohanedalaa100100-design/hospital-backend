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
        Schema::create('medical_profiles', function (Blueprint $table) {
            $table->id();
            // ربط البروفايل بالمستخدم (كل مستخدم له بروفايل طبي واحد فقط)
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade'); 
            
<<<<<<< HEAD
            // Basic Information (كما في التصميم)
            $table->string('name'); 
            $table->integer('age');
            $table->string('gender'); // Male / Female
=======
            // Basic Information (جعلناها nullable لأن اليوزر بيكملها بعد التسجيل)
            $table->string('full_name')->nullable(); 
            $table->integer('age')->nullable();
            $table->string('gender')->nullable(); // Male / Female
>>>>>>> 6e711c13bab2bed39e1645776aff93cbfa13765e
            
            // Medical Info
            $table->string('blood_type')->nullable(); // O+, A+, etc.
            $table->text('chronic_diseases')->nullable(); // سكر، ضغط، قلب
            $table->text('allergies')->nullable(); // حساسية من أدوية أو أطعمة
            $table->string('special_condition')->nullable(); // Pregnant / Special Needs
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_profiles');
    }
};