<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon_url'];

    
    protected function iconUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                if (filter_var($value, FILTER_VALIDATE_URL)) return $value;
                return asset('images/specialties/' . $value);
            },
        );
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