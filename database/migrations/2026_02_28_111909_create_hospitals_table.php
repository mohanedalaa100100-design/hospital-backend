<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable(); 

            $table->enum('type', ['government', 'private'])->default('government');
            $table->string('image_url')->nullable();

            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            $table->text('emergency_days')->nullable();
            $table->string('working_hours')->default('24/7'); //

            
            $table->string('rating')->nullable();
            $table->string('accreditation')->nullable(); 
            $table->text('about')->nullable(); 

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};