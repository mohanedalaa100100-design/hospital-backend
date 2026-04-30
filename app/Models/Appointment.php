<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'clinic_id',
        'appointment_date',
        'appointment_day',  
        'appointment_time',
        'time_slot',        
        'patient_name',
        'patient_phone',
        'doc_fees',         
        'service_fees',     
        'total_amount',    
        'payment_method',   
        'status',
        'notes'
    ];

   
    public function user()
    {
        return $this->belongsTo(User::class);
    }

   
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

  
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

   
    public function hospital()
    {
        return $this->hasOneThrough(
            Hospital::class,
            Clinic::class,
            'id',         
            'id',         
            'clinic_id',   
            'hospital_id'   
        );
    }
}