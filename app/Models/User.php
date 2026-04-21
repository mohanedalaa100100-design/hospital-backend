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
        'role',
        'otp',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_verified'       => 'boolean',
    ];

    public function medicalProfile()
    {
        return $this->hasOne(MedicalProfile::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function emergencyRequests()
    {
        return $this->hasMany(EmergencyRequest::class);
    }

    
}