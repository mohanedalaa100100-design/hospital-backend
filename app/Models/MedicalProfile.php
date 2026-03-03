<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalProfile extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتخزينها مباشرة (Mass Assignment)
     * دي مطابقة تماماً للشاشات اللي في التصميم (UI/UX)
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'age',
        'gender',
        'blood_type',
        'chronic_diseases',
        'allergies',
        'special_condition'
    ];

    /**
     * العلاقة العكسية مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}