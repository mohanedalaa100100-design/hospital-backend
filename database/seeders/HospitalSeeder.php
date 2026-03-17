<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use App\Models\specialty; 
use Illuminate\Support\Facades\Schema;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Hospital::truncate();
        specialty::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. أيقونات التخصصات
        $specialtiesList = [
            'Cardiology'  => 'cardio.png',
            'Emergency'   => 'emergency.png',
            'Orthopedics' => 'ortho.png',
            'Surgery'     => 'surgery.png',
            'Pediatrics'  => 'pedia.png',
            'Neurology'   => 'neuro.png',
        ];

        // 2. قائمة جميع المستشفيات بلا استثناء
        $hospitals = [
            // القاهرة
            ['name' => 'قصر العيني الجامعي', 'type' => 'government', 'lat' => 30.0305, 'lng' => 31.2274, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى الدمرداش العام', 'type' => 'government', 'lat' => 30.0750, 'lng' => 31.2820, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى السلام الدولي', 'type' => 'private', 'lat' => 29.9880, 'lng' => 31.2320, 'days' => '24/7', 'image' => ''],

            // الإسكندرية
            ['name' => 'المستشفى الميري الجامعي', 'type' => 'government', 'lat' => 31.2040, 'lng' => 29.9100, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى رأس التين العام', 'type' => 'government', 'lat' => 31.2000, 'lng' => 29.8700, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى أندلسية سموحة', 'type' => 'private', 'lat' => 31.2210, 'lng' => 29.9500, 'days' => '24/7', 'image' => ''],

            // الجيزة
            ['name' => 'مستشفى أم المصريين الجامعي', 'type' => 'government', 'lat' => 30.0035, 'lng' => 31.2130, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى بولاق الدكرور العام', 'type' => 'government', 'lat' => 30.0300, 'lng' => 31.1900, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى دار الفؤاد', 'type' => 'private', 'lat' => 30.0020, 'lng' => 30.9660, 'days' => '24/7', 'image' => ''],

            // القليوبية
            ['name' => 'مستشفى بنها الجامعي', 'type' => 'government', 'lat' => 30.4600, 'lng' => 31.1800, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى العبور التخصصي العام', 'type' => 'government', 'lat' => 30.2370, 'lng' => 31.4741, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى فريد حبيب', 'type' => 'private', 'lat' => 30.2015, 'lng' => 31.4428, 'days' => '24/7', 'image' => ''],

            // الدقهلية
            ['name' => 'مستشفى الطوارئ الجامعي', 'type' => 'government', 'lat' => 31.0450, 'lng' => 31.3550, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى المنصورة العام الدولي', 'type' => 'government', 'lat' => 31.0500, 'lng' => 31.3700, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى النيل التخصصي', 'type' => 'private', 'lat' => 31.0420, 'lng' => 31.3650, 'days' => '24/7', 'image' => ''],

            // الغربية
            ['name' => 'مستشفى طنطا الجامعي', 'type' => 'government', 'lat' => 30.7910, 'lng' => 31.0010, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى المنشاوي العام', 'type' => 'government', 'lat' => 30.7850, 'lng' => 31.0050, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى الدلتا الدولي', 'type' => 'private', 'lat' => 30.7890, 'lng' => 30.9990, 'days' => '24/7', 'image' => ''],

            // الشرقية
            ['name' => 'مستشفى الزقازيق الجامعي', 'type' => 'government', 'lat' => 30.5870, 'lng' => 31.5030, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى الأحرار العام', 'type' => 'government', 'lat' => 30.5700, 'lng' => 31.4900, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى العبور بالزقازيق', 'type' => 'private', 'lat' => 30.5910, 'lng' => 31.5080, 'days' => '24/7', 'image' => ''],

            // المنيا
            ['name' => 'مستشفى المنيا الجامعي', 'type' => 'government', 'lat' => 28.1100, 'lng' => 30.7500, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى المنيا العام', 'type' => 'government', 'lat' => 28.1150, 'lng' => 30.7550, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى الراعي الصالح', 'type' => 'private', 'lat' => 28.1080, 'lng' => 30.7450, 'days' => '24/7', 'image' => ''],

            // سوهاج
            ['name' => 'مستشفى سوهاج الجامعي', 'type' => 'government', 'lat' => 26.5570, 'lng' => 31.6948, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى سوهاج العام', 'type' => 'government', 'lat' => 26.5600, 'lng' => 31.7000, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى الهلال بسوهاج', 'type' => 'private', 'lat' => 26.5550, 'lng' => 31.6900, 'days' => '24/7', 'image' => ''],

            // قنا
            ['name' => 'مستشفى قنا الجامعي', 'type' => 'government', 'lat' => 26.1640, 'lng' => 32.7270, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى قنا العام', 'type' => 'government', 'lat' => 26.1700, 'lng' => 32.7300, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى طيبة التخصصي', 'type' => 'private', 'lat' => 26.1600, 'lng' => 32.7200, 'days' => '24/7', 'image' => ''],

            // الأقصر
            ['name' => 'مستشفى الكرنك الجامعي الدولي', 'type' => 'government', 'lat' => 25.7100, 'lng' => 32.6500, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى الأقصر العام', 'type' => 'government', 'lat' => 25.6984, 'lng' => 32.6396, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى الأقصر الدولي (خاص)', 'type' => 'private', 'lat' => 25.6900, 'lng' => 32.6300, 'days' => '24/7', 'image' => ''],

            // أسوان
            ['name' => 'مستشفى أسوان الجامعي', 'type' => 'government', 'lat' => 24.0889, 'lng' => 32.8998, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى أسوان العام', 'type' => 'government', 'lat' => 24.0900, 'lng' => 32.9000, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى دريم بأسوان', 'type' => 'private', 'lat' => 24.0950, 'lng' => 32.9050, 'days' => '24/7', 'image' => ''],

            // الفيوم
            ['name' => 'مستشفى الفيوم الجامعي', 'type' => 'government', 'lat' => 29.3084, 'lng' => 30.8428, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى الفيوم العام', 'type' => 'government', 'lat' => 29.3100, 'lng' => 30.8500, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى الزهراء بالفيوم', 'type' => 'private', 'lat' => 29.3050, 'lng' => 30.8400, 'days' => '24/7', 'image' => ''],

            // بني سويف
            ['name' => 'مستشفى بني سويف الجامعي', 'type' => 'government', 'lat' => 29.0753, 'lng' => 31.0980, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى بني سويف العام', 'type' => 'government', 'lat' => 29.0800, 'lng' => 31.1000, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى المودة', 'type' => 'private', 'lat' => 29.0720, 'lng' => 31.1020, 'days' => '24/7', 'image' => ''],

            // كفر الشيخ
            ['name' => 'مستشفى كفر الشيخ الجامعي', 'type' => 'government', 'lat' => 31.1107, 'lng' => 30.9388, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى كفر الشيخ العام', 'type' => 'government', 'lat' => 31.1150, 'lng' => 30.9450, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى العبور التخصصي', 'type' => 'private', 'lat' => 31.1090, 'lng' => 30.9380, 'days' => '24/7', 'image' => ''],

            // دمياط
            ['name' => 'مستشفى دمياط التخصصي الجامعي', 'type' => 'government', 'lat' => 31.4175, 'lng' => 31.8144, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى دمياط العام', 'type' => 'government', 'lat' => 31.4200, 'lng' => 31.8200, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى دار الشفاء بدمياط', 'type' => 'private', 'lat' => 31.4150, 'lng' => 31.8100, 'days' => '24/7', 'image' => ''],

            // بورسعيد
            ['name' => 'مستشفى بورسعيد الجامعي', 'type' => 'government', 'lat' => 31.2590, 'lng' => 32.2910, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى النصر العام', 'type' => 'government', 'lat' => 31.2600, 'lng' => 32.3000, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى آل سليمان', 'type' => 'private', 'lat' => 31.2550, 'lng' => 32.2850, 'days' => '24/7', 'image' => ''],

            // الإسماعيلية
            ['name' => 'مستشفى جامعة قناة السويس', 'type' => 'government', 'lat' => 30.6210, 'lng' => 32.2680, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى الإسماعيلية العام', 'type' => 'government', 'lat' => 30.6000, 'lng' => 32.2700, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى هيئة قناة السويس', 'type' => 'private', 'lat' => 30.5950, 'lng' => 32.2650, 'days' => '24/7', 'image' => ''],

            // السويس
            ['name' => 'مستشفى السويس الجامعي', 'type' => 'government', 'lat' => 29.9660, 'lng' => 32.5490, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى السويس العام', 'type' => 'government', 'lat' => 29.9700, 'lng' => 32.5550, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى السويس التخصصي', 'type' => 'private', 'lat' => 29.9600, 'lng' => 32.5400, 'days' => '24/7', 'image' => ''],

            // المنوفية
            ['name' => 'مستشفى شبين الكوم الجامعي', 'type' => 'government', 'lat' => 30.5611, 'lng' => 31.0111, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى شبين الكوم العام', 'type' => 'government', 'lat' => 30.5500, 'lng' => 31.1400, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى المواساة بشبين', 'type' => 'private', 'lat' => 30.5590, 'lng' => 31.0080, 'days' => '24/7', 'image' => ''],

            // البحيرة
            ['name' => 'مستشفى دمنهور الجامعي التعليمي', 'type' => 'government', 'lat' => 31.0365, 'lng' => 30.4700, 'days' => 'Saturday,Monday,Wednesday', 'image' => ''],
            ['name' => 'مستشفى دمنهور العام', 'type' => 'government', 'lat' => 31.1300, 'lng' => 30.0600, 'days' => 'Sunday,Tuesday,Thursday,Friday', 'image' => ''],
            ['name' => 'مستشفى الشروق بدمنهور', 'type' => 'private', 'lat' => 31.0380, 'lng' => 30.4750, 'days' => '24/7', 'image' => ''],
        ];

        foreach ($hospitals as $data) {
            // منطق الصور: لو الخانة فاضية، حط صورة افتراضية بناءً على النوع
            $imageToSave = !empty($data['image']) ? $data['image'] : 
                (($data['type'] === 'private') 
                    ? 'https://img.freepik.com/free-vector/private-hospital-building.jpg' 
                    : 'https://img.freepik.com/free-vector/hospital-building-concept-illustration_114360-8440.jpg');

            $hospital = Hospital::create([
                'name'           => $data['name'],
                'type'           => $data['type'],
                'address'        => 'مصر - ' . $data['name'],
                'phone'          => '01' . rand(0, 2) . rand(10000000, 99999999),
                'lat'            => $data['lat'],
                'lng'            => $data['lng'],
                'emergency_days' => $data['days'],
                'is_active'      => true,
                'is_featured'    => ($data['type'] === 'private'), 
                'image_url'      => $imageToSave, 
            ]);

            // ربط التخصصات
            if ($data['type'] === 'government') {
                $specsToAssign = ['Emergency', 'Surgery', 'Orthopedics'];
            } else {
                $specsToAssign = array_keys($specialtiesList);
            }

            foreach ($specsToAssign as $specName) {
                specialty::create([
                    'hospital_id' => $hospital->id,
                    'name'        => $specName,
                    'icon_url'    => $specialtiesList[$specName],
                ]);
            }
        }
    }
}