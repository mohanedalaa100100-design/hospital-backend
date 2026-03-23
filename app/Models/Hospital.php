<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // 👈 لازم تضيف السطر ده فوق

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

    /**
     * Accessor: بيحول مسار الصورة لرابط كامل تلقائياً 🔥
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                
                // لو المسار يبدأ بـ http (زي صور النت القديمة) سيبه زي ما هو
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }
                
                // لو مسار محلي، ضيف له رابط السيرفر (asset)
                return asset($value);
            },
        );
    }

    /**
     * علاقة Many To Many مع التخصصات
     */
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class);
    }

    /**
     * علاقة الخدمات الطبية
     */
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class);
    }
}