<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyRequest extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتخزينها (Mass Assignment)
     */
    protected $fillable = [
        'user_id', 
        'hospital_id', 
        'user_lat', 
        'user_lng', 
        'status', 
        'note'
    ];

    /**
     * علاقة الطلب بالمستخدم (المريض)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة الطلب بالمستشفى المختارة
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}