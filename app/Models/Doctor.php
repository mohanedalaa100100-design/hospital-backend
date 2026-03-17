<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    // الحقول اللي مسموح تتملي أوتوماتيك [cite: 2026-03-09]
    protected $fillable = [
        'name', 
        'specialty', 
        'phone', 
        'experience_years', 
        'rating', 
        'image_url', 
        'hospital_id'
    ];

    // دي العلاقة اللي الـ Error بيطلبها بالاسم
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}