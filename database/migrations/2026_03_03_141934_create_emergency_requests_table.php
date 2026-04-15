<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_requests', function (Blueprint $table) {
            $table->id();
            
            
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->foreignId('hospital_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('user_lat', 10, 8);
            $table->decimal('user_lng', 11, 8);
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_requests');
    }
};