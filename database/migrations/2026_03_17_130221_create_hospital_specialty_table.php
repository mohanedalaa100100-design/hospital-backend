<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_specialty', function (Blueprint $table) {
            $table->id();

            
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade');

            
            $table->foreignId('specialty_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_specialty');
    }
};