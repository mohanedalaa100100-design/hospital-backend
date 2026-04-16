<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon_url'];

    /**
     * علاقة Many-to-Many مع المستشفيات
     * حددنا اسم الجدول الوسيط لضمان استرجاع البيانات لشروق
     */
    public function hospitals()
    {
        return $this->belongsToMany(Hospital::class, 'hospital_specialty');
    }

    /**
     * علاقة One-to-Many مع الأطباء
     */
    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}