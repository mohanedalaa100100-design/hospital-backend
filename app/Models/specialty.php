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
                if (!$value) {
                    return url('images/specialties/default.png');
                }
                
            
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }

               
                return url('images/specialties/' . $value);
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