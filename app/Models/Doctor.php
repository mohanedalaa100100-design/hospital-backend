<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
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
        'available_slots'  => 'array', 
        'working_days'     => 'array', 
        'is_available'     => 'boolean',
        'rating'           => 'decimal:2',
        'consultation_fee' => 'decimal:2',
        'experience_years' => 'integer',
        'reviews_count'    => 'integer',
    ];

    
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                
                if (!$value) {
                    return asset('images/doctors/male_avatar.png');
                }
                
                
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }
                
                
                return asset($value);
            },
        );
    }

    /**
     * علاقة الدكتور بالعيادة
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * علاقة الدكتور بالتخصص
     */
    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    /**
     * الوصول للمستشفى بشكل غير مباشر من خلال العيادة
     */
    public function hospital()
    {
        return $this->hasOneThrough(
            Hospital::class, 
            Clinic::class, 
            'id',           // Foreign key on Clinic table
            'id',           // Foreign key on Hospital table
            'clinic_id',    // Local key on Doctor table
            'hospital_id'   // Local key on Clinic table
        );
    }

    /**
     * علاقة المواعيد التابعة للدكتور
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}