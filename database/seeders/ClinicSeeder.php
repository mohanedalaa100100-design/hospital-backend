<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use App\Models\Specialty;
use App\Models\Clinic;

class ClinicSeeder extends Seeder
{
    
    public function run(): void
    {
        $hospitals = Hospital::all();
        $specialties = Specialty::all();
        
        
        $imageIndex = 1;

        foreach ($hospitals as $hospital) {
            foreach ($specialties as $specialty) {
                Clinic::create([
                    'hospital_id'  => $hospital->id,
                    'specialty_id' => $specialty->id,
                    'name'         => "clinic {$specialty->name} - {$hospital->name}",
                    'address'      => $hospital->address,
                    'phone'        => $hospital->phone,
                    
                    
                    'image_url'    => $imageIndex . '.jfif', 
                    
                    'is_active'    => true,
                    'lat'          => $hospital->lat,
                    'lng'          => $hospital->lng,
                ]);

                
                $imageIndex++;
                if ($imageIndex > 6) {
                    $imageIndex = 1;
                }
            }
        }
    }
}