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
        // 1. تنظيف الجداول عشان نمنع التكرار
        Schema::disableForeignKeyConstraints();
        Specialty::truncate();
        MedicalService::truncate();
        Schema::enableForeignKeyConstraints();

        $hospitals = Hospital::all();
        if ($hospitals->isEmpty()) {
            return;
        }

        // 2. قائمة التخصصات الـ 6 الأساسية (زي ما في الـ UI بالظبط)
        $specialtiesList = [
            ['name' => 'Cardiology', 'icon' => 'cardio.png'],
            ['name' => 'Orthopedics', 'icon' => 'ortho.png'],
            ['name' => 'Oncology', 'icon' => 'oncology.png'],
            ['name' => 'Internal Medicine', 'icon' => 'internal.png'],
            ['name' => 'Kidney Transplant', 'icon' => 'kidney.png'],
            ['name' => 'Neurology', 'icon' => 'neuro.png'],
        ];

        // 3. إنشاء التخصصات الـ 6
        foreach ($specialtiesList as $specialtyData) {
            Specialty::create([
                'name' => $specialtyData['name'],
                'icon_url' => $specialtyData['icon']
            ]);
        }

        $allSpecialties = Specialty::all();

        // 4. الخدمات الـ 6 (2 Supportive + 4 Facilities) عشان شروق تقسمهم
        $servicesList = [
            // Supportive Medical Services
            ['name' => 'ICU CARE', 'desc' => 'Intensive Monitoring'],
            ['name' => 'Laboratory', 'desc' => 'Advanced Diagnostics'],
            
            // Facilities & Services
            ['name' => 'Private Rooms', 'desc' => 'Equipped with Wi-Fi and TV'],
            ['name' => 'On-site Amenities', 'desc' => 'Pharmacy, ATM, and Cafe'],
            ['name' => '24/7 Pharmacy', 'desc' => 'Emergency Medications'],
            ['name' => 'Patient Parking', 'desc' => 'Secure Parking Space'],
        ];

        foreach ($hospitals as $hospital) {
            // تحديث بيانات المستشفى الأساسية
            $hospital->update([
                'rating' => 4.2, // خليته ثابت زي التصميم أو ممكن تخليه راندوم
                'accreditation' => 'JCI Accredited',
                'whatsapp' => '01100216370', // الرقم اللي في التصميم
                'working_hours' => 'Available 24/7',
                'about' => "WELCOME TO {$hospital->name}\nExcellence in Medical Care."
            ]);

            // ربط كل التخصصات الـ 6 بالمستشفى عشان يظهروا كلهم
            $hospital->specialties()->sync($allSpecialties->pluck('id')->toArray());

            // إضافة الـ 6 خدمات لكل مستشفى
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