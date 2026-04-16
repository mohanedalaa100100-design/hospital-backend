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
        // 1. تنظيف جدول التخصصات والخدمات عشان نمنع أي تكرار قديم
        Schema::disableForeignKeyConstraints();
        Specialty::truncate();
        MedicalService::truncate();
        Schema::enableForeignKeyConstraints();

        $hospitals = Hospital::all();
        if ($hospitals->isEmpty()) {
            return;
        }

        // 2. قائمة التخصصات الـ 6 الأساسية (دي اللي هتظهر لشروق في الـ Home)
        $specialtiesList = [
            ['name' => 'Cardiology', 'icon' => 'cardio.png'],
            ['name' => 'Orthopedics', 'icon' => 'ortho.png'],
            ['name' => 'Neurology', 'icon' => 'neuro.png'],
            ['name' => 'Pediatrics', 'icon' => 'pedia.png'],
            ['name' => 'Emergency', 'icon' => 'emergency.png'],
            ['name' => 'Surgery', 'icon' => 'surgery.png'],
        ];

        $servicesList = [
            ['name' => 'ICU', 'desc' => '24/7 Intensive Care Unit'],
            ['name' => 'Emergency', 'desc' => 'Fast response medical team'],
            ['name' => 'Laboratory', 'desc' => 'Accurate blood and clinical tests'],
        ];

        // 3. إنشاء التخصصات الـ 6 لمرة واحدة فقط في الداتابيز كلها
        foreach ($specialtiesList as $specialtyData) {
            Specialty::create([
                'name' => $specialtyData['name'],
                'icon_url' => $specialtyData['icon']
            ]);
        }

        $allSpecialties = Specialty::all();

        foreach ($hospitals as $hospital) {
            // 4. تحديث بيانات المستشفى
            $hospital->update([
                'rating' => '4.' . rand(5, 9),
                'accreditation' => 'JCI Accredited',
                'whatsapp' => '011' . rand(10000000, 99999999),
                'working_hours' => 'Available 24/7',
                'about' => "Welcome to {$hospital->name}. Expert medical care."
            ]);

            // 5. الربط الصح (Many-to-Many): كل مستشفى تاخد 5 تخصصات عشوائية من الـ 6 اللي فوق
            $hospital->specialties()->sync(
                $allSpecialties->random(5)->pluck('id')->toArray()
            );

            // 6. إضافة الخدمات
            $randomServices = collect($servicesList)->random(2);
            foreach ($randomServices as $service) {
                MedicalService::create([
                    'hospital_id' => $hospital->id,
                    'name' => $service['name'],
                    'description' => $service['desc']
                ]);
            }
        }
    }
}