<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickAction extends Model
{
    use HasFactory;

    // بنحدد الأعمدة اللي مسموح نملى بياناتها من الـ Seeder أو الـ API
    protected $fillable = [
        'title',       // العنوان (Emergency Mode / Normal Mode)
        'description', // الوصف اللي تحت العنوان
        'image_url',   // مسار الصورة أو الأيقونة
        'type'         // نوع الأكشن (emergency أو normal)
    ];
}