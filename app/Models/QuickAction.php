<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // 👈 السطر ده أساسي للـ Accessor

class QuickAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',       
        'description', 
        'image_url',   
        'type'         
    ];

    /**
     * Accessor: بيحول اسم الصورة لرابط كامل تلقائياً 🚀
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;

                // لو المسار متخزن كرابط كامل (http) سيبه زي ما هو
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }

                // بيرجع الرابط بناءً على المسار في public/images/quick_actions
                // تأكد إن الصور موجودة في الفولدر ده
                return asset('images/quick_actions/' . $value);
            },
        );
    }
}