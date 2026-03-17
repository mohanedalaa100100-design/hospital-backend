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
        'hospital_id',
        'appointment_date',
        'appointment_time',
        'patient_name',
        'patient_phone',
        'status',
        'notes'
    ];

    /**
     * الموعد ينتمي لمستخدم (المريض اللي عامل Login)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الموعد ينتمي لدكتور معين
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * الموعد يتم في مستشفى معينة
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}