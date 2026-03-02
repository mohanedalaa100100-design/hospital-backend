<?php

namespace Database\Seeders;

use App\Models\QuickAction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuickActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إضافة الزرار الأول (Emergency Mode) بناءً على Figma
        QuickAction::create([
            'title' => 'Emergency Mode',
            'description' => 'Immediate response and location tracking',
            'image_url' => 'emergency_bg.png',
            'type' => 'emergency'
        ]);

        // إضافة الزرار الثاني (Normal Mode) بناءً على Figma
        QuickAction::create([
            'title' => 'Normal Mode',
            'description' => 'Routine operations and patient care',
            'image_url' => 'normal_bg.png',
            'type' => 'normal'
        ]);
    }
}