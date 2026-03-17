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

            $table->unsignedBigInteger('hospital_id'); // ربط التخصص بالمستشفى
            $table->string('name'); // اسم التخصص زي (Cardiology, Oncology)
            $table->string('icon_url')->nullable(); // أيقونة التخصص

            $table->timestamps();

            // إنشاء المفتاح الخارجي
            $table->foreign('hospital_id')
                  ->references('id')
                  ->on('hospitals')
                  ->onDelete('cascade');
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