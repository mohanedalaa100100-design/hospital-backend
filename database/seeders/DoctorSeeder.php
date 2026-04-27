<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Specialty;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('hospital_specialty')->truncate();
        Doctor::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $maleAvatar   = 'images/doctors/male_avatar.png';
        $femaleAvatar = 'images/doctors/female_avatar.png';

        $maleNames   = ['Ahmed', 'Mohamed', 'Mahmoud', 'Ali', 'Hassan', 'Ibrahim', 'Kareem', 'Mostafa', 'Omar', 'Youssef', 'Hany', 'Amr'];
        $femaleNames = ['Sarah', 'Mona', 'Aya', 'Noura', 'Fatma', 'Yasmine', 'Heba', 'Dina', 'Laila', 'Mariam', 'Sohila'];
        $lastNames   = ['Hassan', 'Ibrahim', 'Khalil', 'Mansour', 'Abdelaziz', 'Zaki', 'Salem', 'Fayed', 'El-Sayed'];

        $allSlots = ['09:00 AM', '10:00 AM', '11:30 AM', '01:00 PM', '03:00 PM', '04:30 PM', '06:00 PM', '08:00 PM'];
        $allDays  = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

    
        $specialtiesData = [
            'Cardiology'        => 11,
            'Orthopedics'       => 11,
            'Neurology'         => 11,
            'Oncology'          => 10,
            'Internal Medicine' => 10,
            'Kidney Transplant' => 10,
        ];

        $hospitals = Hospital::all();
        if ($hospitals->isEmpty()) return;

        
        Specialty::whereNotIn('name', array_keys($specialtiesData))->delete();

        $hospitalIndex = 0;

        foreach ($specialtiesData as $specName => $limit) {
            
            $iconName = strtolower(str_replace(' ', '_', $specName)) . '.png';
            if($specName == 'Cardiology') $iconName = 'heart.png';
            if($specName == 'Orthopedics') $iconName = 'bones.png';
            if($specName == 'Internal Medicine') $iconName = 'stethscope.png';
            if($specName == 'Kidney Transplant') $iconName = 'kidneys.png';

            $specialty = Specialty::firstOrCreate(
                ['name' => $specName],
                ['icon_url' => $iconName]
            );

            
            for ($i = 0; $i < $limit; $i++) {
                if (isset($hospitals[$hospitalIndex])) {
                    $hospital = $hospitals[$hospitalIndex];
                    
                    
                    $hospital->specialties()->sync([$specialty->id]);

                    
                    for ($d = 1; $d <= 3; $d++) {
                        $isFemale = ($d == 3);
                        $fName    = $isFemale ? fake()->randomElement($femaleNames) : fake()->randomElement($maleNames);
                        $lName    = fake()->randomElement($lastNames);

                        Doctor::create([
                            'hospital_id'      => $hospital->id,
                            'specialty_id'     => $specialty->id,
                            'name'             => 'Dr. ' . $fName . ' ' . $lName,
                            'title'            => 'Consultant ' . $specName,
                            'experience_years' => rand(5, 20),
                            'bio'              => "Expert professional in {$specName} with extensive experience.",
                            'image'            => $isFemale ? $femaleAvatar : $maleAvatar,
                            'consultation_fee' => fake()->randomElement([250, 350, 500, 600]),
                            'available_slots'  => fake()->randomElements($allSlots, rand(2, 4)),
                            'working_days'     => fake()->randomElements($allDays, rand(2, 4)),
                            'is_available'     => true,
                        ]);
                    }
                    $hospitalIndex++;
                }
            }
        }
    }
}