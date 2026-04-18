<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Hospital;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        
        $manualDoctors = [
            ['hospital_id' => 1, 'name' => 'Dr. Ahmed Hassan', 'specialty' => 'Cardiology', 'title' => 'Senior Cardiologist', 'experience_years' => 15, 'rating' => 4.9, 'reviews_count' => 128],
            ['hospital_id' => 2, 'name' => 'Dr. Mahmoud Magdy', 'specialty' => 'Cardiology', 'title' => 'Consultant', 'experience_years' => 20, 'rating' => 5.0, 'reviews_count' => 210],
            ['hospital_id' => 1, 'name' => 'Dr. Sarah El-Menshawy', 'specialty' => 'Dentistry', 'title' => 'Dental Surgeon', 'experience_years' => 8, 'rating' => 4.7, 'reviews_count' => 56],
            ['hospital_id' => 1, 'name' => 'Dr. Ali Mansour', 'specialty' => 'Neurology', 'title' => 'Neurology Specialist', 'experience_years' => 12, 'rating' => 4.8, 'reviews_count' => 94],
            ['hospital_id' => 2, 'name' => 'Dr. Ibrahim Rizk', 'specialty' => 'Orthopedics', 'title' => 'Orthopedic Consultant', 'experience_years' => 18, 'rating' => 4.9, 'reviews_count' => 112],
            ['hospital_id' => 1, 'name' => 'Dr. Mona Zaki', 'specialty' => 'Pediatrics', 'title' => 'Pediatrician', 'experience_years' => 10, 'rating' => 4.6, 'reviews_count' => 75],
            ['hospital_id' => 2, 'name' => 'Dr. Hala Fawzy', 'specialty' => 'Ophthalmology', 'title' => 'Eye Specialist', 'experience_years' => 14, 'rating' => 4.8, 'reviews_count' => 88],
        ];

        foreach ($manualDoctors as $doctor) {
            Doctor::updateOrCreate(['name' => $doctor['name']], $doctor);
        }

        
        $specialties = ['Cardiology', 'Dentistry', 'Neurology', 'Orthopedics', 'Pediatrics', 'Ophthalmology'];
        $allHospitals = Hospital::all();

        foreach ($allHospitals as $hospital) {
            foreach ($specialties as $specialty) {
                
                Doctor::firstOrCreate(
                    ['hospital_id' => $hospital->id, 'specialty' => $specialty],
                    [
                        
                        'name' => 'Dr. ' . fake()->name(), 
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