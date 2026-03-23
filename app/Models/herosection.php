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

    /**
     * Accessor: بيقرأ الصور من فولدر المستشفيات مباشرة 🔥
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;

                // لو المسار فيه http سيبه زي ما هو
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }

                // هنا بنخليه يقرأ من public/images/hospitals علطول
                return asset('images/hospitals/' . $value);
            },
        );
    }
}