<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Hospital;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        // 1. الدكاترة اللي إنت اخترتهم يدوياً (الـ 7 الأساسيين) [cite: 2026-03-09]
        $manualDoctors = [
            ['hospital_id' => 1, 'name' => 'د. أحمد حسان', 'specialty' => 'Cardiology', 'title' => 'Senior Cardiologist', 'experience_years' => 15, 'rating' => 4.9, 'reviews_count' => 128],
            ['hospital_id' => 2, 'name' => 'د. محمود مجدي', 'specialty' => 'Cardiology', 'title' => 'Consultant', 'experience_years' => 20, 'rating' => 5.0, 'reviews_count' => 210],
            ['hospital_id' => 1, 'name' => 'د. سارة المنشاوي', 'specialty' => 'Dentistry', 'title' => 'Dental Surgeon', 'experience_years' => 8, 'rating' => 4.7, 'reviews_count' => 56],
            ['hospital_id' => 1, 'name' => 'د. علي منصور', 'specialty' => 'Neurology', 'title' => 'Neurology Specialist', 'experience_years' => 12, 'rating' => 4.8, 'reviews_count' => 94],
            ['hospital_id' => 2, 'name' => 'د. إبراهيم رزق', 'specialty' => 'Orthopedics', 'title' => 'Orthopedic Consultant', 'experience_years' => 18, 'rating' => 4.9, 'reviews_count' => 112],
            ['hospital_id' => 1, 'name' => 'د. منى زكي', 'specialty' => 'Pediatrics', 'title' => 'Pediatrician', 'experience_years' => 10, 'rating' => 4.6, 'reviews_count' => 75],
            ['hospital_id' => 2, 'name' => 'د. هالة فوزي', 'specialty' => 'Ophthalmology', 'title' => 'Eye Specialist', 'experience_years' => 14, 'rating' => 4.8, 'reviews_count' => 88],
        ];

        foreach ($manualDoctors as $doctor) {
            Doctor::updateOrCreate(['name' => $doctor['name']], $doctor);
        }

        // 2. توزيع تلقائي لباقي المستشفيات عشان الأبلكيشن ميبقاش فاضي
        $specialties = ['Cardiology', 'Dentistry', 'Neurology', 'Orthopedics', 'Pediatrics', 'Ophthalmology'];
        $allHospitals = Hospital::all();

        foreach ($allHospitals as $hospital) {
            foreach ($specialties as $specialty) {
                // بنتحقق لو المستشفى دي ناقصها التخصص ده نكمله [cite: 2026-03-09]
                Doctor::firstOrCreate(
                    ['hospital_id' => $hospital->id, 'specialty' => $specialty],
                    [
                        'name' => 'د. ' . fake('ar_SA')->name(), // أسماء عربية [cite: 2026-03-09]
                        'title' => 'Specialist ' . $specialty,
                        'experience_years' => fake()->numberBetween(5, 20),
                        'rating' => fake()->randomFloat(1, 4, 5),
                        'reviews_count' => fake()->numberBetween(10, 100),
                    ]
                );
            }
        }
    }
}