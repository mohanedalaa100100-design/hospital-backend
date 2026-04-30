<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use App\Models\Specialty;      
use App\Models\MedicalService; 
use App\Models\Clinic; 
use Illuminate\Support\Facades\Schema;

class HospitalDetailsSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Specialty::truncate();
        MedicalService::truncate();
        Clinic::truncate(); 
        Schema::enableForeignKeyConstraints();

        $hospitals = Hospital::all();
        if ($hospitals->isEmpty()) {
            $this->command->warn("No hospitals found. Please run HospitalSeeder first.");
            return;
        }

        
        $specialtiesList = [
            ['name' => 'Cardiology', 'icon' => 'images/specialties/heart.png'],
            ['name' => 'Orthopedics', 'icon' => 'images/specialties/bones.png'],
            ['name' => 'Oncology', 'icon' => 'images/specialties/oncology.png'],
            ['name' => 'Internal Medicine', 'icon' => 'images/specialties/stethoscope.png'],
            ['name' => 'Kidney Transplant', 'icon' => 'images/specialties/kidneys.png'],
            ['name' => 'Neurology', 'icon' => 'images/specialties/neurology.png'],
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

                Clinic::updateOrCreate([
                    'hospital_id'  => $hospital->id,
                    'specialty_id' => $specialty->id,
                ], [
                    'name'         => "{$specialty->name} Clinic - {$hospital->name}",
                    'address'      => $hospital->address,
                    'phone'        => $hospital->phone,
                    'is_active'    => true,
                ]);
            }
        }

        $remainingHospitals = $shuffledHospitals->slice(60);
        foreach ($remainingHospitals as $hospital) {
            $randomSpecs = $allSpecialties->random(2);
            foreach ($randomSpecs as $spec) {
                $hospital->specialties()->syncWithoutDetaching([$spec->id]);

                Clinic::updateOrCreate([
                    'hospital_id'  => $hospital->id,
                    'specialty_id' => $spec->id,
                ], [
                    'name'         => "{$spec->name} Clinic - {$hospital->name}",
                    'address'      => $hospital->address,
                    'phone'        => $hospital->phone,
                    'is_active'    => true,
                ]);
            }
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

        $this->command->info('Seed completed: Hospitals, Specialties, and Clinics created successfully.');
    }
}