<?php

namespace Database\Seeders;

use App\Models\QuickAction;
use Illuminate\Database\Seeder;

class QuickActionSeeder extends Seeder
{
    public function run(): void
    {
        QuickAction::updateOrCreate(
            ['type' => 'emergency'],
            [
                'title' => 'Emergency Mode',
                'description' => 'Immediate response and location tracking',
                'image_url' => 'emergency_bg.png',
            ]
        );

        QuickAction::updateOrCreate(
            ['type' => 'normal'],
            [
                'title' => 'Normal Mode',
                'description' => 'Routine operations and patient care',
                'image_url' => 'normal_bg.png',
            ]
        );
    }
}