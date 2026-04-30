<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\Clinic;

class DoctorSeeder extends Seeder
{
    
    public function run(): void
    {
    
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Doctor::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        
        $maleAvatar   = 'images/doctors/male_avatar.png';
        $femaleAvatar = 'images/doctors/female_avatar.png';

        
        $maleNames   = ['Ahmed', 'Mohamed', 'Mahmoud', 'Ali', 'Hassan', 'Ibrahim', 'Kareem', 'Mostafa', 'Omar', 'Youssef', 'Hany', 'Amr'];
        $femaleNames = ['Sarah', 'Mona', 'Aya', 'Noura', 'Fatma', 'Yasmine', 'Heba', 'Dina', 'Laila', 'Mariam', 'Sohila'];
        $lastNames   = ['Hassan', 'Ibrahim', 'Khalil', 'Mansour', 'Abdelaziz', 'Zaki', 'Salem', 'Fayed', 'El-Sayed'];

        
        $allSlots = ['09:00 AM', '10:00 AM', '11:30 AM', '01:00 PM', '03:00 PM', '04:30 PM', '06:00 PM', '08:00 PM'];
        $allDays  = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

        
        $specialties = Specialty::all();

        foreach ($specialties as $specialty) {
            
            
            $clinics = Clinic::where('specialty_id', $specialty->id)->get();

            foreach ($clinics as $clinic) {
                
                
                for ($d = 1; $d <= 2; $d++) {
                    $isFemale = ($d == 2);
                    $fName    = $isFemale ? fake()->randomElement($femaleNames) : fake()->randomElement($maleNames);
                    $lName    = fake()->randomElement($lastNames);

                    Doctor::create([
                        'clinic_id'        => $clinic->id,
                        'specialty_id'     => $specialty->id,
                        'name'             => 'Dr. ' . $fName . ' ' . $lName,
                        'title'            => 'Consultant ' . $specialty->name,
                        'experience_years' => rand(5, 25),
                        'bio'              => "Expert professional in {$specialty->name} with extensive clinical experience at {$clinic->name}.",
                        'image'            => $isFemale ? $femaleAvatar : $maleAvatar,
                        'consultation_fee' => fake()->randomElement([250, 300, 400, 500, 600]),
                        'available_slots'  => $allSlots, 
                        'working_days'     => $allDays, 
                        'is_available'     => true,
                    ]);
                }
            }
        }
    }
}