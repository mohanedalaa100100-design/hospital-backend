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
        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            // الربط مع جدول المستشفيات (Foreign Key)
            // لو حذفت مستشفى، كل تخصصاتها هتتحذف تلقائياً (cascade)
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade'); 
            
            $table->string('name'); // اسم التخصص زي (Cardiology, Oncology)
            $table->string('icon_url')->nullable(); // عشان لو حبيت تحط أيقونة زي اللي في Figma
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialties');
    }
};
