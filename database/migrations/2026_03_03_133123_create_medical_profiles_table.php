<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('medical_profiles', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade'); 
            
        
            $table->string('full_name')->nullable(); 
            $table->integer('age')->nullable();
            $table->string('gender')->nullable(); 
            
            
            $table->string('blood_type')->nullable(); 
            $table->text('chronic_diseases')->nullable(); 
            $table->text('allergies')->nullable(); 
            $table->string('special_condition')->nullable(); 
            
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('medical_profiles');
    }
};