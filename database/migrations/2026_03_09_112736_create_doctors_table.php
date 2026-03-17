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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade');
            $table->string('name'); 
            $table->string('specialty'); 
            $table->string('title'); 
            $table->integer('experience_years'); 
            $table->string('phone')->nullable(); 
            $table->decimal('rating', 3, 2)->default(4.5); 
            $table->integer('reviews_count')->default(0); 
            $table->string('image')->nullable(); 
            $table->boolean('is_available')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};