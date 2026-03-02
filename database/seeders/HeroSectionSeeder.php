<?php

namespace Database\Seeders;

use App\Models\herosection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HeroSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تسجيل بيانات سكشن الـ Hero الموجود في التصميم
        herosection::create([
            'title' => 'Emergency medical assistance when every second counts',
            'description' => 'Connecting you to life-saving care and reliable medical information instantly. Your safety and peace of mind is our top priority',
            'image_url' => 'hero_ambulance.png', // اسم ملف الصورة اللي في التصميم
        ]);
    }
}