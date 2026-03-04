<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * الخانات اللي مسموح نكتب فيها بيانات مرة واحدة (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'phone',    // الحقل الجديد اللي ضفناه عشان الطوارئ والـ OTP
        'password',
        'is_admin', // عشان نقدر نحدد مين الأدمن ومين اليوزر العادي
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean', // عشان لارافيل يرجعها true/false بدل 1/0
    ];

    /**
     * العلاقة مع البروفايل الطبي (One-to-One)
     * دي بتخليك تنادي $user->medicalProfile وتجيب كل بياناته الطبية فوراً
     */
    public function medicalProfile()
    {
        return $this->hasOne(MedicalProfile::class);
    }
}