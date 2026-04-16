<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'phone', 'type', 'image_url', 
        'lat', 'lng', 'emergency_days', 'is_active', 
        'is_featured', 'rating', 'accreditation', 
        'whatsapp', 'working_hours', 'about'
    ];

    // علاقة الـ Many-to-Many المظبوطة مع التخصصات
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'hospital_specialty');
    }

    // العلاقات التانية (One-to-Many)
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class);
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}