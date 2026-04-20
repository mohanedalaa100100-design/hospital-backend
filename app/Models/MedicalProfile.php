<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalProfile extends Model
{
    use HasFactory;

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


    protected $casts = [
        'chronic_diseases' => 'array', 
        'allergies'        => 'array',
        'age'              => 'integer',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function getBloodTypeAttribute($value)
    {
        return strtoupper($value);
    }
}