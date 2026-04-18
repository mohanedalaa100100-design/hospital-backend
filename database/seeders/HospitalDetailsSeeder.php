<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use App\Models\Specialty;      
use App\Models\MedicalService; 
use Illuminate\Support\Facades\Schema;

class HospitalDetailsSeeder extends Seeder
{
    public function run(): void
    {
        
        Schema::disableForeignKeyConstraints();
        Specialty::truncate();
        MedicalService::truncate();
        Schema::enableForeignKeyConstraints();

        $hospitals = Hospital::all();
        if ($hospitals->isEmpty()) {
            return;
        }

        
        $specialtiesList = [
            ['name' => 'Cardiology', 'icon' => 'cardio.png'],
            ['name' => 'Orthopedics', 'icon' => 'ortho.png'],
            ['name' => 'Oncology', 'icon' => 'oncology.png'],
            ['name' => 'Internal Medicine', 'icon' => 'internal.png'],
            ['name' => 'Kidney Transplant', 'icon' => 'kidney.png'],
            ['name' => 'Neurology', 'icon' => 'neuro.png'],
        ];

        
        foreach ($specialtiesList as $specialtyData) {
            Specialty::create([
                'name' => $specialtyData['name'],
                'icon_url' => $specialtyData['icon']
            ]);
        }

        $allSpecialties = Specialty::all();

        
        $servicesList = [
            
            ['name' => 'ICU CARE', 'desc' => 'Intensive Monitoring'],
            ['name' => 'Laboratory', 'desc' => 'Advanced Diagnostics'],
            
            
            ['name' => 'Private Rooms', 'desc' => 'Equipped with Wi-Fi and TV'],
            ['name' => 'On-site Amenities', 'desc' => 'Pharmacy, ATM, and Cafe'],
            ['name' => '24/7 Pharmacy', 'desc' => 'Emergency Medications'],
            ['name' => 'Patient Parking', 'desc' => 'Secure Parking Space'],
        ];

        foreach ($hospitals as $hospital) {
            
            $hospital->update([
                'rating' => fake()->randomFloat(1, 4, 5),
                'accreditation' => 'JCI Accredited',
                'whatsapp' => '01' . fake()->numberBetween(10000000, 99999999),
                'working_hours' => 'Available 24/7',
                'about' => "WELCOME TO {$hospital->name}\nExcellence in Medical Care."
            ]);

            
            $hospital->specialties()->sync($allSpecialties->pluck('id')->toArray());

            
            foreach ($servicesList as $service) {
                MedicalService::create([
                    'hospital_id' => $hospital->id,
                    'name' => $service['name'],
                    'description' => $service['desc']
                ]);
            }
        }
    }
}