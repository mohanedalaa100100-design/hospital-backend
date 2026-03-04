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
        // 1. تكريت مستخدم "أدمن" (ليه صلاحية يضيف ويمسح مستشفيات)
        User::updateOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'System Admin',
                'phone' => '01012345678',
                'password' => Hash::make('admin123'), // الباسورد اللي هتدخل بيه
                'is_admin' => true, // الخانة اللي بتفتحه الـ Middleware
            ]
        );

        // 2. تكريت مستخدم "عادي" (للتجربة كأنه مريض)
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Normal User',
                'phone' => '01122334455',
                'password' => Hash::make('user123'),
                'is_admin' => false,
            ]
        );

        // 3. استدعاء سدرز المستشفيات بالترتيب الصح
        $this->call([
            HospitalSeeder::class,        // يكريت المستشفيات الأساسية
            HospitalDetailsSeeder::class, // يضيف التخصصات والخدمات للمستشفيات اللي اتكريتت
        ]);
    }
}