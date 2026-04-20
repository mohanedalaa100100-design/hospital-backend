<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyRequest extends Model
{
    use HasFactory;

  
    protected $fillable = [
        'user_id',       
        'hospital_id', 
        'lat',           
        'lng', 
        'guest_name',    
        'guest_phone',   
        'status', 
        'note'
    ];

  
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}