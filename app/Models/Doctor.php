<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; 

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'specialty_id', 
        'name', 
        'title', 
        'experience_years', 
        'bio',
        'phone', 
        'rating', 
        'reviews_count',
        'image', 
        'consultation_fee',
        'available_slots', 
        'working_days',    
        'is_available'
    ];

  
    protected $casts = [
        'available_slots' => 'array', 
        'working_days'    => 'array', 
        'is_available'    => 'boolean',
        'rating'          => 'decimal:2',
    ];

   
    protected function image(): Attribute
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

   
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

   
    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}