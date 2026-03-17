<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role', // 'user' أو 'hospital' أو 'admin'
        'otp',  // مهم عشان شاشات التحقق في الـ UI
    ];

    protected $hidden = [
        'password',
        'remember_token',
        // 'role', // شيلته من هنا عشان الفرونت إند محتاجه يعرف صلاحيات اليوزر
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // 1. العلاقة مع الملف الطبي (لشاشات الطوارئ)
    public function medicalProfile()
    {
        return $this->hasOne(MedicalProfile::class);
    }

    // 2. العلاقة مع الحجوزات (لشاشة My Appointments)
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // 3. العلاقة مع طلبات الطوارئ (SOS Requests)
    public function emergencyRequests()
    {
        return $this->hasMany(EmergencyRequest::class);
    }
}