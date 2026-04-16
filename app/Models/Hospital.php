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

    
    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'rating' => 'float',
        'lat' => 'double',
        'lng' => 'double',
    ];

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'hospital_specialty');
    }

    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class);
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    
    public function getImageUrlAttribute($value)
    {
        if (!$value) {
            return asset('images/hospitals/default.jpg');
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? $value : asset($value);
    }

    
     
    public function getRatingAttribute($value)
    {
        return number_format((float) ($value ?? 0.0), 1);
    }
}