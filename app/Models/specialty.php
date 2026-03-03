<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class specialty extends Model
{
    use HasFactory;

    protected $table = 'specialties'; 

    // السماح بإضافة البيانات دي لقاعدة البيانات
    protected $fillable = [
        'hospital_id',
        'name',
        'icon_url'
    ];

    /**
     * علاقة التخصص بالمستشفى (كل تخصص ينتمي لمستشفى واحدة)
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}