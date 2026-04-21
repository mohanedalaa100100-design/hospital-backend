<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalService extends Model
{
    use HasFactory;

    protected $table = 'medical_services';

    protected $fillable = [
        'hospital_id',
        'name',
        'icon_url',
        'description'
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}