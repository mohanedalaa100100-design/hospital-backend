<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon_url'];

  
    public function getIconUrlAttribute($value)
    {
    
        if (!$value) {
            return asset('images/specialties/stethoscope.png');
        }

        $fileName = basename($value);

    
        return asset('images/specialties/' . $fileName);
    }

    public function hospitals()
    {
        return $this->belongsToMany(Hospital::class, 'hospital_specialty');
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}