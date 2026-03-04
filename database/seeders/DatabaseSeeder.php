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
        // التعديل اللي عملناه: استخدام updateOrCreate عشان لو رنيت السدر 100 مرة ميكررش البيانات
        
        // 1. تكريت مستخدم "أدمن"
        $admin = User::updateOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'System Admin',
                'phone' => '01012345678',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );
        // حركة صايعة: بنمسح أي توكنز قديمة للأدمن عشان يبدأ على نظافة
        $admin->tokens()->delete();

        // 2. تكريت مستخدم "عادي"
        $user = User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Normal User',
                'phone' => '01122334455',
                'password' => Hash::make('user123'),
                'is_admin' => false,
            ]
        );
        $user->tokens()->delete();

        // 3. استدعاء سدرز المستشفيات
        $this->call([
            HospitalSeeder::class,
            HospitalDetailsSeeder::class,
        ]);
    }
}