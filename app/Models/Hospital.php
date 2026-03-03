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
        'image_url',
        'lat',
        'lng',
        'is_featured',
        'rating',            // التقييم (زي 4.2)
        'accreditation',     // الاعتماد (JCI)
        'whatsapp',          // رقم الواتساب
        'working_hours',     // مواعيد العمل
        'about'              // نص الترحيب
    ];

    /**
     * علاقة المستشفى بالتخصصات (One-to-Many)
     */
    public function specialties()
    {
        return $this->hasMany(specialty::class);
    }

    /**
     * علاقة المستشفى بالخدمات الطبية (One-to-Many)
     */
    public function medicalServices()
    {
        return $this->hasMany(medicalservice::class);
    }
}