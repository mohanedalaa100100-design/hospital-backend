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
        // 1. إنشاء حساب الأدمن (System Admin)
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

        // 2. إنشاء حساب مستخدم عادي (Mohanad Alaa)
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

        // 3. استدعاء باقي السدرز بالترتيب المنطقي
        $this->call([
            // بيانات الواجهة (Hero & Quick Actions)
            HeroSectionSeeder::class,   // مهم جداً عشان السلايدر اللي فوق
            QuickActionSeeder::class,    // مهم عشان زراير Emergency/Normal mode

            // البيانات الأساسية (المستشفيات أولاً)
            HospitalSeeder::class,

            // البيانات المعتمدة على المستشفيات (التفاصيل والدكاترة)
            HospitalDetailsSeeder::class,
            DoctorSeeder::class, 
        ]);
    }
}