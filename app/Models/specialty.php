<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; 

class Specialty extends Model
{
    use HasFactory;

    // تحديد اسم الجدول في قاعدة البيانات
    protected $table = 'specialties'; 

    protected $fillable = [
        'name',
        'icon_url'
    ];

    /**
     * Accessor للـ icon_url عشان يرجع المسار كامل
     */
    protected function iconUrl(): Attribute
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

    
    public function hospitals()
    {
        return $this->belongsToMany(Hospital::class, 'hospital_specialty');
    }

   
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'specialty_id');
    }
}