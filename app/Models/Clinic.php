<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'specialty_id',
        'name',
        'address',
        'phone',
        'image_url',
        'lat',
        'lng',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat'       => 'double',
        'lng'       => 'double',
    ];

   
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                
                if (!$value) {
                    return asset('images/Clinic/1.jfif');
                }

                
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }

                
                return asset('images/Clinic/' . $value);
            },
        );
    }

    
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

  
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    
    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

 
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}