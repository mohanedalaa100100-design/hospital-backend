<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; 

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'specialty_id', 
        'phone', 
        'experience_years', 
        'rating', 
        'image_url', 
        'hospital_id'
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

    
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}