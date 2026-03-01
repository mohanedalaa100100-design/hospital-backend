<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    // السطر ده هو اللي بيسمح لـ Laravel بإضافة البيانات دي لقاعدة البيانات
    protected $fillable = [
        'name',
        'address',
        'image_url',
        'lat',
        'lng',
        'is_featured'
    ];
}