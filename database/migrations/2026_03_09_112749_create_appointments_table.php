<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade');
            
            
            $table->date('appointment_date');            
            $table->string('appointment_day');          
            $table->string('appointment_time');         
            $table->string('time_slot')->default('morning'); 
            
            
            $table->string('patient_name');
            $table->string('patient_phone');
            
            
            $table->decimal('doc_fees', 8, 2);         
            $table->decimal('service_fees', 8, 2)->default(20.00);
            $table->decimal('total_amount', 8, 2);     
            
            
            $table->string('payment_method')->default('hospital'); 
            $table->string('status')->default('pending');         
            $table->text('notes')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};