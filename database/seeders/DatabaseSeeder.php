<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. إنشاء حساب الأدمن
        $admin = User::updateOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'System Admin',
                'phone' => '01012345678',
                'password' => Hash::make('admin123'),
                'role' => 'admin', 
            ]
        );
        $admin->tokens()->delete();

        // 2. إنشاء حساب المستخدم 
        $user = User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Mohanad Alaa',
                'phone' => '01122334455',
                'password' => Hash::make('user123'),
                'role' => 'user',
            ]
        );
        $user->tokens()->delete();

    
        $this->call([
            
            HeroSectionSeeder::class,   
            QuickActionSeeder::class,    

            
            HospitalSeeder::class,

        
            HospitalDetailsSeeder::class,

            DoctorSeeder::class, 
        ]);
    }
}