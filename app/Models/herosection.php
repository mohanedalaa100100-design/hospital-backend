<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class herosection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_url'
    ];

    
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;

                
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }

                
                return asset('images/hospitals/' . $value);
            },
        );
    }
}