<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; 

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
        'rating',
        'accreditation',
        'whatsapp',
        'working_hours',
        'about'
    ];

    
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }
                
                return asset($value);
            },
        );
    }

    
     
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class);
    }

    
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class);
    }

   
    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}