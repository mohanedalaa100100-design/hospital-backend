<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // السطر ده هو اللي بيستدعي ملف المستشفيات
        $this->call([
            HospitalSeeder::class,
        ]);
    }
}