<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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