<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use App\Models\specialty;
use App\Models\medicalservice;

class HospitalDetailsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. هنجيب كل المستشفيات اللي إنت ضفتها قبل كدة
        $hospitals = Hospital::all();

        // 2. قائمة بتخصصات متنوعة عشان نوزعها
        $specialtiesList = [
            ['name' => 'Cardiology', 'icon' => 'cardio.png'],
            ['name' => 'Orthopedics', 'icon' => 'ortho.png'],
            ['name' => 'Neurology', 'icon' => 'neuro.png'],
            ['name' => 'Pediatrics', 'icon' => 'pedia.png'],
            ['name' => 'Oncology', 'icon' => 'cancer.png'],
        ];

        // 3. قائمة بخدمات طبية
        $servicesList = [
            ['name' => 'ICU', 'desc' => '24/7 Intensive Care'],
            ['name' => 'Emergency', 'desc' => 'Fast response team'],
            ['name' => 'Laboratory', 'desc' => 'Accurate blood tests'],
            ['name' => 'Pharmacy', 'desc' => 'Open 24 hours'],
        ];

        foreach ($hospitals as $hospital) {
            // تحديث بيانات المستشفى الأساسية (Rating, WhatsApp, etc)
            $hospital->update([
                'rating' => '4.' . rand(1, 9), // تقييم عشوائي 4.1 لـ 4.9
                'accreditation' => 'JCI Accredited',
                'whatsapp' => '011' . rand(10000000, 99999999),
                'working_hours' => 'Available 24/7',
                'about' => "Welcome to {$hospital->name}. We provide high-quality medical services."
            ]);

            // إضافة 3 تخصصات عشوائية لكل مستشفى
            foreach (array_rand($specialtiesList, 3) as $key) {
                specialty::updateOrCreate(
                    ['hospital_id' => $hospital->id, 'name' => $specialtiesList[$key]['name']],
                    ['icon_url' => $specialtiesList[$key]['icon']]
                );
            }

            // إضافة خدمتين عشوائيتين لكل مستشفى
            foreach (array_rand($servicesList, 2) as $key) {
                medicalservice::updateOrCreate(
                    ['hospital_id' => $hospital->id, 'name' => $servicesList[$key]['name']],
                    ['description' => $servicesList[$key]['desc']]
                );
            }
        }
    }
}
