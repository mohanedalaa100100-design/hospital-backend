<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use Illuminate\Support\Facades\Schema;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clear hospital table
        Schema::disableForeignKeyConstraints();
        Hospital::truncate();
        Schema::enableForeignKeyConstraints();

        
        $images = [
            'images/hospitals/1.jpg',
            'images/hospitals/2.jpg',
            'images/hospitals/3.jpg',
            'images/hospitals/4.webp',
            'images/hospitals/5.jpg',
            'images/hospitals/6.jpg',
            'images/hospitals/7.jpg',
            'images/hospitals/8.webp',
            'images/hospitals/9.jpg',
            'images/hospitals/10.jpg',
        ];

        
        $hospitals = [
            // Cairo
            ['name' => 'Kasr Al-Ainy University Hospital', 'type' => 'government', 'lat' => 30.0305, 'lng' => 31.2274, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Al-Demerdash General Hospital', 'type' => 'government', 'lat' => 30.0750, 'lng' => 31.2820, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'As-Salam International Hospital', 'type' => 'private', 'lat' => 29.9880, 'lng' => 31.2320, 'days' => '24/7'],
            // Alexandria
            ['name' => 'Alexandria Main University Hospital', 'type' => 'government', 'lat' => 31.2040, 'lng' => 29.9100, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Ras El Tin General Hospital', 'type' => 'government', 'lat' => 31.2000, 'lng' => 29.8700, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Andalusia Hospital Smouha', 'type' => 'private', 'lat' => 31.2210, 'lng' => 29.9500, 'days' => '24/7'],
            // Giza
            ['name' => 'Om El-Masryeen General Hospital', 'type' => 'government', 'lat' => 30.0035, 'lng' => 31.2130, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Bulaq El Dakrour Hospital', 'type' => 'government', 'lat' => 30.0300, 'lng' => 31.1900, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Dar Al Fouad Hospital', 'type' => 'private', 'lat' => 30.0020, 'lng' => 30.9660, 'days' => '24/7'],
            // Qalyubia
            ['name' => 'Benha University Hospital', 'type' => 'government', 'lat' => 30.4600, 'lng' => 31.1800, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Obour Specialized Hospital', 'type' => 'government', 'lat' => 30.2370, 'lng' => 31.4741, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Farid Habib Hospital', 'type' => 'private', 'lat' => 30.2015, 'lng' => 31.4428, 'days' => '24/7'],
            // Dakahlia
            ['name' => 'Mansoura Emergency Hospital', 'type' => 'government', 'lat' => 31.0450, 'lng' => 31.3550, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Mansoura International Hospital', 'type' => 'government', 'lat' => 31.0500, 'lng' => 31.3700, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Al-Nile Specialized Hospital', 'type' => 'private', 'lat' => 31.0420, 'lng' => 31.3650, 'days' => '24/7'],
            // Gharbia
            ['name' => 'Tanta University Hospital', 'type' => 'government', 'lat' => 30.7910, 'lng' => 31.0010, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Al-Menshawy General Hospital', 'type' => 'government', 'lat' => 30.7850, 'lng' => 31.0050, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Delta International Hospital', 'type' => 'private', 'lat' => 30.7890, 'lng' => 30.9990, 'days' => '24/7'],
            // Sharqia
            ['name' => 'Zagazig University Hospital', 'type' => 'government', 'lat' => 30.5870, 'lng' => 31.5030, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Al-Ahrar General Hospital', 'type' => 'government', 'lat' => 30.5700, 'lng' => 31.4900, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Obour Hospital Zagazig', 'type' => 'private', 'lat' => 30.5910, 'lng' => 31.5080, 'days' => '24/7'],
            // Minya
            ['name' => 'Minya University Hospital', 'type' => 'government', 'lat' => 28.1100, 'lng' => 30.7500, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Minya General Hospital', 'type' => 'government', 'lat' => 28.1150, 'lng' => 30.7550, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Good Shepherd Hospital', 'type' => 'private', 'lat' => 28.1080, 'lng' => 30.7450, 'days' => '24/7'],
            // Sohag
            ['name' => 'Sohag University Hospital', 'type' => 'government', 'lat' => 26.5570, 'lng' => 31.6948, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Sohag General Hospital', 'type' => 'government', 'lat' => 26.5600, 'lng' => 31.7000, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Al-Hilal Hospital Sohag', 'type' => 'private', 'lat' => 26.5550, 'lng' => 31.6900, 'days' => '24/7'],
            // Qena
            ['name' => 'Qena University Hospital', 'type' => 'government', 'lat' => 26.1640, 'lng' => 32.7270, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Qena General Hospital', 'type' => 'government', 'lat' => 26.1700, 'lng' => 32.7300, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Taiba Specialized Hospital', 'type' => 'private', 'lat' => 26.1600, 'lng' => 32.7200, 'days' => '24/7'],
            // Luxor
            ['name' => 'Karnak International Hospital', 'type' => 'government', 'lat' => 25.7100, 'lng' => 32.6500, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Luxor General Hospital', 'type' => 'government', 'lat' => 25.6984, 'lng' => 32.6396, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Luxor International Private Hospital', 'type' => 'private', 'lat' => 25.6900, 'lng' => 32.6300, 'days' => '24/7'],
            // Aswan
            ['name' => 'Aswan University Hospital', 'type' => 'government', 'lat' => 24.0889, 'lng' => 32.8998, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Aswan General Hospital', 'type' => 'government', 'lat' => 24.0900, 'lng' => 32.9000, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Dream Hospital Aswan', 'type' => 'private', 'lat' => 24.0950, 'lng' => 32.9050, 'days' => '24/7'],
            // Fayoum
            ['name' => 'Fayoum University Hospital', 'type' => 'government', 'lat' => 29.3084, 'lng' => 30.8428, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Fayoum General Hospital', 'type' => 'government', 'lat' => 29.3100, 'lng' => 30.8500, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Al-Zahraa Hospital Fayoum', 'type' => 'private', 'lat' => 29.3050, 'lng' => 30.8400, 'days' => '24/7'],
            // Beni Suef
            ['name' => 'Beni Suef University Hospital', 'type' => 'government', 'lat' => 29.0753, 'lng' => 31.0980, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Beni Suef General Hospital', 'type' => 'government', 'lat' => 29.0800, 'lng' => 31.1000, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Al-Mawada Private Hospital', 'type' => 'private', 'lat' => 29.0720, 'lng' => 31.1020, 'days' => '24/7'],
            // Kafr El Sheikh
            ['name' => 'Kafr El Sheikh University Hospital', 'type' => 'government', 'lat' => 31.1107, 'lng' => 30.9388, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Kafr El Sheikh General Hospital', 'type' => 'government', 'lat' => 31.1150, 'lng' => 30.9450, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Obour Specialized Hospital Kafr El Sheikh', 'type' => 'private', 'lat' => 31.1090, 'lng' => 30.9380, 'days' => '24/7'],
            // Damietta
            ['name' => 'Damietta Specialized University Hospital', 'type' => 'government', 'lat' => 31.4175, 'lng' => 31.8144, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Damietta General Hospital', 'type' => 'government', 'lat' => 31.4200, 'lng' => 31.8200, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Dar Al Shifa Hospital Damietta', 'type' => 'private', 'lat' => 31.4150, 'lng' => 31.8100, 'days' => '24/7'],
            // Port Said
            ['name' => 'Port Said University Hospital', 'type' => 'government', 'lat' => 31.2590, 'lng' => 32.2910, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Al-Nasr General Hospital', 'type' => 'government', 'lat' => 31.2600, 'lng' => 32.3000, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Al-Soliman Hospital', 'type' => 'private', 'lat' => 31.2550, 'lng' => 32.2850, 'days' => '24/7'],
            // Ismailiya
            ['name' => 'Suez Canal University Hospital', 'type' => 'government', 'lat' => 30.6210, 'lng' => 32.2680, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Ismailiya General Hospital', 'type' => 'government', 'lat' => 30.6000, 'lng' => 32.2700, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Suez Canal Authority Hospital', 'type' => 'private', 'lat' => 30.5950, 'lng' => 32.2650, 'days' => '24/7'],
            // Suez
            ['name' => 'Suez University Hospital', 'type' => 'government', 'lat' => 29.9660, 'lng' => 32.5490, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Suez General Hospital', 'type' => 'government', 'lat' => 29.9700, 'lng' => 32.5550, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Suez Specialized Hospital', 'type' => 'private', 'lat' => 29.9600, 'lng' => 32.5400, 'days' => '24/7'],
            // Monufia
            ['name' => 'Shebin El Kom University Hospital', 'type' => 'government', 'lat' => 30.5611, 'lng' => 31.0111, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Shebin El Kom General Hospital', 'type' => 'government', 'lat' => 30.5500, 'lng' => 31.1400, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Al-Mowasa Hospital Shebin', 'type' => 'private', 'lat' => 30.5590, 'lng' => 31.0080, 'days' => '24/7'],
            // Beheira
            ['name' => 'Damanhour Educational Hospital', 'type' => 'government', 'lat' => 31.0365, 'lng' => 30.4700, 'days' => 'Saturday,Monday,Wednesday'],
            ['name' => 'Damanhour General Hospital', 'type' => 'government', 'lat' => 31.1300, 'lng' => 30.0600, 'days' => 'Sunday,Tuesday,Thursday,Friday'],
            ['name' => 'Al-Shorouk Hospital Damanhour', 'type' => 'private', 'lat' => 31.0380, 'lng' => 30.4750, 'days' => '24/7'],
        ];

        $imageCount = count($images);

        // 4. Create Hospitals
        foreach ($hospitals as $index => $data) {
            Hospital::create([
                'name'           => $data['name'],
                'type'           => $data['type'],
                'address'        => 'Egypt - ' . $data['name'],
                'phone'          => '01' . rand(0, 2) . rand(10000000, 99999999),
                'lat'            => $data['lat'],
                'lng'            => $data['lng'],
                'emergency_days' => $data['days'],
                'is_active'      => true,
                'is_featured'    => ($data['type'] === 'private'), 
                'image_url'      => $images[$index % $imageCount],
            ]);
        }
    }
}