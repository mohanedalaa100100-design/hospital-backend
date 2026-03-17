<?php

namespace Database\Seeders;

use App\Models\herosection; // تعديل: خليته صغير زي الصورة
use Illuminate\Database\Seeder;

class HeroSectionSeeder extends Seeder
{
    public function run(): void
    {
        herosection::updateOrCreate(
            ['title' => 'Emergency medical assistance when every second counts'],
            [
                'description' => 'Connecting you to life-saving care and reliable medical information instantly. Your safety and peace of mind is our top priority',
                'image_url' => 'hero_ambulance.png', 
            ]
        );
    }
}