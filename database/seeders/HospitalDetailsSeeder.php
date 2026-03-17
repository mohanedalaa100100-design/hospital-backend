<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use App\Models\Specialty;      
use App\Models\MedicalService; 

class HospitalDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $hospitals = Hospital::all();

        if ($hospitals->isEmpty()) {
            return;
        }

        $specialtiesList = [
            ['name' => 'Cardiology', 'icon' => 'cardio.png'],
            ['name' => 'Orthopedics', 'icon' => 'ortho.png'],
            ['name' => 'Neurology', 'icon' => 'neuro.png'],
            ['name' => 'Pediatrics', 'icon' => 'pedia.png'],
            ['name' => 'Emergency', 'icon' => 'emergency.png'],
        ];

        $servicesList = [
            ['name' => 'ICU', 'desc' => '24/7 Intensive Care Unit'],
            ['name' => 'Emergency', 'desc' => 'Fast response medical team'],
            ['name' => 'Laboratory', 'desc' => 'Accurate blood and clinical tests'],
        ];

        // أولاً: إنشاء جميع التخصصات في جدول specialties
        foreach ($specialtiesList as $specialtyData) {
            Specialty::updateOrCreate(
                ['name' => $specialtyData['name']],
                ['icon_url' => $specialtyData['icon']]
            );
        }

        $allSpecialties = Specialty::all();

        foreach ($hospitals as $hospital) {
            // تحديث بيانات المستشفى
            $hospital->update([
                'rating' => '4.' . rand(1, 9),
                'accreditation' => 'JCI Accredited',
                'whatsapp' => '011' . rand(10000000, 99999999),
                'working_hours' => 'Available 24/7',
                'about' => "Welcome to {$hospital->name}. Expert medical care."
            ]);

            // ربط المستشفى بـ 3 تخصصات عشوائية
            $hospital->specialties()->sync(
                $allSpecialties->random(3)->pluck('id')->toArray()
            );

            // إضافة الخدمات (One-to-Many)
            $randomServices = collect($servicesList)->random(2);
            foreach ($randomServices as $service) {
                MedicalService::updateOrCreate(
                    ['hospital_id' => $hospital->id, 'name' => $service['name']],
                    ['description' => $service['desc']]
                );
            }
        }
    }
}