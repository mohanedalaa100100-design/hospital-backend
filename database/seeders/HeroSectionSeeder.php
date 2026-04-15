<?php

namespace Database\Seeders;

use App\Models\herosection; 
use Illuminate\Database\Seeder;

class HeroSectionSeeder extends Seeder
{
    public function run(): void
    {
        herosection::updateOrCreate(
            ['title' => 'قصر العيني الجامعي'],
            [
                'description' => 'Connecting you to life-saving care and reliable medical information instantly. Your safety and peace of mind is our top priority',
                
                'image_url' => '1.jpg', 
            ]
        );
    }
}