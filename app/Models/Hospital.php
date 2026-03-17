<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'type',
        'image_url',
        'lat',
        'lng',
        'emergency_days',
        'is_active',
        'is_featured',

        // optional fields
        'rating',
        'accreditation',
        'whatsapp',
        'working_hours',
        'about'
    ];

    /**
     * علاقة Many To Many مع التخصصات 🔥
     */
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class);
    }

    /**
     * علاقة الخدمات الطبية (ممكن تسيبها زي ما هي)
     */
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class);
    }
}