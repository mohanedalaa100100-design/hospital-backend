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
            
        
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->restrictOnDelete();

            $table->foreignId('clinic_id')
                ->constrained('clinics')
                ->restrictOnDelete(); 

           
            $table->date('appointment_date');
            $table->string('appointment_time');
      

          
            $table->string('patient_name');
            $table->string('patient_phone');

         
            $table->decimal('doc_fees', 10, 2); 
            $table->decimal('service_fees', 10, 2)->default(20.00); 
            $table->decimal('total_amount', 10, 2); 

        
            $table->enum('payment_method', [
                'clinic',  
                'card',     
                'wallet',   
                'insurance' 
            ])->nullable();

            $table->timestamp('paid_at')->nullable();

        
            $table->enum('status', [
                'pending',   
                'confirmed',  
                'completed',  
                'cancelled'   
            ])->default('pending');

           
            $table->text('notes')->nullable();

         
            $table->timestamps(); 

         
            $table->unique(
                ['doctor_id', 'appointment_date', 'appointment_time'],
                'unique_doctor_appointment_slot'
            );

        
            $table->index('user_id');
            $table->index('doctor_id');
            $table->index('clinic_id');
            $table->index('appointment_date');
            $table->index('status');
            $table->index('patient_phone');

            
            $table->index(['doctor_id', 'appointment_date']);

            
            $table->index(['user_id', 'appointment_date']);
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};