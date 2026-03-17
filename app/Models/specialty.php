<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $table = 'specialties'; 

    protected $fillable = [
        'name',
        'icon_url'
    ];

    /**
     * علاقة Many To Many مع المستشفيات
     */
    public function hospitals()
    {
        return $this->belongsToMany(Hospital::class);
    }

    /**
     * علاقة التخصص بالدكاترة (One To Many)
     */
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'specialty_id');
    }
}