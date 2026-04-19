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
            $this->command->warn("No hospitals found. Please run HospitalSeeder first.");
            return;
        }

        
        $specialtiesList = [
            ['name' => 'Cardiology', 'icon' => 'heart.png'],
            ['name' => 'Orthopedics', 'icon' => 'bones.png'],
            ['name' => 'Oncology', 'icon' => 'oncology.png'],
            ['name' => 'Internal Medicine', 'icon' => 'stethoscope.png'],
            ['name' => 'Kidney Transplant', 'icon' => 'kidneys.png'],
            ['name' => 'Neurology', 'icon' => 'neurology.png'],
        ];

        foreach ($specialtiesList as $specialtyData) {
            Specialty::create([
                'name' => $specialtyData['name'],
                'icon_url' => $specialtyData['icon']
            ]);
        }

        $allSpecialties = Specialty::all();
        
    
        $shuffledHospitals = $hospitals->shuffle();

        
        foreach ($allSpecialties as $index => $specialty) {
            
            $slice = $shuffledHospitals->slice($index * 10, 10);
            
            foreach ($slice as $hospital) {
                $hospital->specialties()->syncWithoutDetaching([$specialty->id]);
            }
        }

        
        $remainingHospitals = $shuffledHospitals->slice(60);
        foreach ($remainingHospitals as $hospital) {
            
            $randomSpecs = $allSpecialties->random(2)->pluck('id')->toArray();
            $hospital->specialties()->syncWithoutDetaching($randomSpecs);
        }

        
        $servicesList = [
            ['name' => 'ICU CARE', 'desc' => 'Intensive Monitoring'],
            ['name' => 'Laboratory', 'desc' => 'Advanced Diagnostics'],
            ['name' => '24/7 Pharmacy', 'desc' => 'Emergency Medications'],
            ['name' => 'Patient Parking', 'desc' => 'Secure Parking Space'],
        ];

        foreach ($hospitals as $hospital) {
            $hospital->update([
                'rating' => fake()->randomFloat(1, 4, 5),
                'accreditation' => 'JCI Accredited',
                'whatsapp' => '01' . fake()->numberBetween(10000000, 99999999),
                'working_hours' => 'Available 24/7',
                'about' => "WELCOME TO {$hospital->name}\nEXCELLENCE IN MEDICAL CARE."
            ]);

            foreach ($servicesList as $service) {
                MedicalService::updateOrCreate([
                    'hospital_id' => $hospital->id,
                    'name' => $service['name']
                ], [
                    'description' => $service['desc']
                ]);
            }
        }

        $this->command->info('63 Hospitals updated: 60 with unique specialties and 3 with mixed.');
    }
}