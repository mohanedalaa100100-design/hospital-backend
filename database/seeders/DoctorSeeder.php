<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Specialty;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $maleAvatar   = 'images/doctors/male_avatar.png';
        $femaleAvatar = 'images/doctors/female_avatar.png';

        $maleNames   = ['Ahmed', 'Mohamed', 'Mahmoud', 'Ali', 'Hassan', 'Ibrahim', 'Kareem', 'Mostafa', 'Omar', 'Youssef', 'Hany', 'Amr'];
        $femaleNames = ['Sarah', 'Mona', 'Aya', 'Noura', 'Fatma', 'Yasmine', 'Heba', 'Dina', 'Laila', 'Mariam', 'Sohila'];
        $lastNames   = ['Hassan', 'Ibrahim', 'Khalil', 'Mansour', 'Abdelaziz', 'Zaki', 'Salem', 'Fayed', 'El-Sayed'];

        $allSlots = ['09:00 AM', '10:00 AM', '11:30 AM', '01:00 PM', '03:00 PM', '04:30 PM', '06:00 PM', '08:00 PM'];
        $allDays  = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

        $specialtiesNames = [
            'Cardiology', 'Dentistry', 'Neurology',
            'Orthopedics', 'Pediatrics', 'Ophthalmology'
        ];

        $hospitals = Hospital::all();
        if ($hospitals->isEmpty()) return;

        foreach ($hospitals as $index => $hospital) {
            $specIndex = floor($index / 10);
            if ($specIndex >= count($specialtiesNames)) $specIndex = count($specialtiesNames) - 1;
            $currentSpecName = $specialtiesNames[$specIndex];

            $specialty = Specialty::firstOrCreate(
                ['name' => $currentSpecName],
                ['icon_url' => 'default_icon.png']
            );

            
            $hospital->specialties()->syncWithoutDetaching([$specialty->id]);

            for ($i = 1; $i <= 3; $i++) {
                $isFemale = ($i == 3);
                $fName    = $isFemale ? fake()->randomElement($femaleNames) : fake()->randomElement($maleNames);
                $lName    = fake()->randomElement($lastNames);

                Doctor::create([
                    'hospital_id'      => $hospital->id,
                    'specialty_id'     => $specialty->id,
                    'name'             => 'Dr. ' . $fName . ' ' . $lName,
                    'title'            => 'Consultant ' . $currentSpecName,
                    'experience_years' => rand(5, 20),
                    'bio'              => "Expert professional in {$currentSpecName} with extensive experience.",
                    'image'            => $isFemale ? $femaleAvatar : $maleAvatar,
                    'consultation_fee' => fake()->randomElement([250, 350, 500, 600]),
                    'available_slots'  => fake()->randomElements($allSlots, rand(2, 4)),
                    'working_days'     => fake()->randomElements($allDays, rand(2, 4)),
                    'is_available'     => true,
                ]);
            }
        }

        $this->seedManualDoctors($hospitals->first()->id, $maleAvatar, $femaleAvatar);
    }

    private function seedManualDoctors($hospitalId, $maleAvatar, $femaleAvatar)
    {
        $hospital = Hospital::find($hospitalId);
        $cardio   = Specialty::firstOrCreate(['name' => 'Cardiology']);
        $pedia    = Specialty::firstOrCreate(['name' => 'Pediatrics']);

        
        $hospital->specialties()->syncWithoutDetaching([$cardio->id, $pedia->id]);

        Doctor::updateOrCreate(['name' => 'Dr. Ahmed Hassan'], [
            'hospital_id'      => $hospitalId,
            'specialty_id'     => $cardio->id,
            'title'            => 'Senior Cardiologist',
            'experience_years' => 15,
            'image'            => $maleAvatar,
            'consultation_fee' => 450,
            'available_slots'  => ['10:00 AM', '01:00 PM', '04:00 PM'],
            'working_days'     => ['Sat', 'Mon', 'Wed'],
            'is_available'     => true,
        ]);

        Doctor::updateOrCreate(['name' => 'Dr. Sarah Mansour'], [
            'hospital_id'      => $hospitalId,
            'specialty_id'     => $pedia->id,
            'title'            => 'Pediatric Consultant',
            'experience_years' => 10,
            'image'            => $femaleAvatar,
            'consultation_fee' => 380,
            'available_slots'  => ['09:00 AM', '11:30 AM', '03:00 PM'],
            'working_days'     => ['Sun', 'Tue', 'Thu'],
            'is_available'     => true,
        ]);
    }
}