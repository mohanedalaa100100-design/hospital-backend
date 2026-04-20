<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_requests', function (Blueprint $request) {
            $request->id();
            $request->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $request->foreignId('hospital_id')->constrained('hospitals')->onDelete('cascade');
            
            
            $request->string('guest_name')->nullable();
            $request->string('guest_phone')->nullable();
            
            $request->decimal('lat', 10, 8);
            $request->decimal('lng', 11, 8);
            $request->text('note')->nullable();
            $request->enum('status', ['pending', 'accepted', 'completed', 'cancelled'])->default('pending');
            $request->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_requests');
    }
};