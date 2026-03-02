<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class herosection extends Model
{
    use HasFactory;

    // بنعرف الأعمدة المسموح بإدخال بيانات فيها (Mass Assignment)
    protected $fillable = [
        'title',
        'description',
        'image_url'
    ];
}
