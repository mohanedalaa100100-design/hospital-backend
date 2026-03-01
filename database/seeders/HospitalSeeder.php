<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. مستشفى القاهرة التخصصي (Featured)
        Hospital::create([
            'name' => 'Cairo Specialized Hospital',
            'address' => 'Nasr City, Cairo',
            'image_url' => 'https://via.placeholder.com/400x250?text=Cairo+Hospital',
            'lat' => 30.0585,
            'lng' => 31.3361,
            'is_featured' => true,
        ]);

        // 2. مستشفى النزهة الدولي (Featured)
        Hospital::create([
            'name' => 'El Nozha International Hospital',
            'address' => 'Sheraton, Heliopolis',
            'image_url' => 'https://via.placeholder.com/400x250?text=Nozha+Hospital',
            'lat' => 30.1111,
            'lng' => 31.3722,
            'is_featured' => true,
        ]);

        // 3. مستشفى دار الفؤاد (Featured)
        Hospital::create([
            'name' => 'Dar Al Fouad Hospital',
            'address' => 'Nasr City Branch',
            'image_url' => 'https://via.placeholder.com/400x250?text=Dar+Al+Fouad',
            'lat' => 30.0667,
            'lng' => 31.3333,
            'is_featured' => true,
        ]);

        // 4. مستشفى السلام الدولي (Normal - للطوارئ فقط)
        Hospital::create([
            'name' => 'As-Salam International',
            'address' => 'Maadi, Corniche El Nil',
            'image_url' => 'https://via.placeholder.com/400x250?text=As-Salam+Hospital',
            'lat' => 29.9602,
            'lng' => 31.2330,
            'is_featured' => false,
        ]);
    }
}