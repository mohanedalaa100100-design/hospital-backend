<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('hospital_id')
                  ->constrained('hospitals')
                  ->onDelete('cascade');
            
            $table->foreignId('specialty_id')
                  ->constrained('specialties')
                  ->onDelete('cascade');
            
            
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->string('address');
            $table->string('phone')->nullable();
            
            
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            
            $table->boolean('is_active')->default(true);
            
            
            $table->timestamps();

            
            $table->index('hospital_id');
            $table->index('specialty_id');
            $table->index('is_active');
            $table->index(['hospital_id', 'specialty_id']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};