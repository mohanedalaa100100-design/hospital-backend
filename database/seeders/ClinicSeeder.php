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
        
        // لو مفيش مستشفيات أو تخصصات، اخرج عشان ميطلعش Error
        if ($hospitals->isEmpty() || $specialties->isEmpty()) {
            return;
        }

        foreach ($hospitals as $hospital) {
            $imageIndex = 1; // بنصفر العداد مع كل مستشفى عشان الصور تتوزع بانتظام

            foreach ($specialties as $specialty) {
                // updateOrCreate بتمنع التكرار نهائياً
                Clinic::updateOrCreate(
                    [
                        'hospital_id'  => $hospital->id,
                        'specialty_id' => $specialty->id,
                    ],
                    [
                        'name'         => "Clinic {$specialty->name} - {$hospital->name}",
                        'address'      => $hospital->address,
                        'phone'        => $hospital->phone,
                        'image_url'    => $imageIndex . '.jfif', 
                        'is_active'    => true,
                        'lat'          => $hospital->lat,
                        'lng'          => $hospital->lng,
                    ]
                );

                $imageIndex++;
                if ($imageIndex > 6) {
                    $imageIndex = 1;
                }
            }
        }
    }
}