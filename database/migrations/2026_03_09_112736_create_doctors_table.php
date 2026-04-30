<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            
            
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('specialty_id')->constrained('specialties')->onDelete('cascade');
            
            $table->string('name');
            $table->string('title'); 
            $table->string('phone')->nullable();
            $table->integer('experience_years');
            $table->text('bio')->nullable();
            
            $table->decimal('rating', 3, 2)->default(4.50);
            $table->integer('reviews_count')->default(0);
            $table->string('image')->nullable(); 
            $table->decimal('consultation_fee', 8, 2)->default(450.00);
            
            
            $table->json('available_slots')->nullable(); 
            $table->json('working_days')->nullable();    
            
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};