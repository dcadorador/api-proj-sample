<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Models\City;
use Carbon\Carbon;

class CitySeeder extends Seeder
{
    public function run()
    {
        $name = [
            'en-us' => 'Abha',
            'ar-sa' => 'أبها'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => 'Buraydah',
            'ar-sa' => 'بريدة'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => 'Dammam',
            'ar-sa' => 'الدمام'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => 'Dhahran',
            'ar-sa' => 'الظهران'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Ha'il",
            'ar-sa' => 'حائل'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Hofuf",
            'ar-sa' => 'الهفوف'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Jeddah",
            'ar-sa' => 'جدة'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Jizan",
            'ar-sa' => 'جازان'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Jubail",
            'ar-sa' => 'الجبيل'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Khobar",
            'ar-sa' => "الخبر"
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Mecca",
            'ar-sa' => 'مكة المكرمة'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Medina",
            'ar-sa' => 'المدينة'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Riyadh",
            'ar-sa' => 'الرياض'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Taif",
            'ar-sa' => 'الطائف'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Tabuk",
            'ar-sa' => 'تبوك'
        ];

        City::create([
            'name' => $name,
        ]);

        $name = [
            'en-us' => "Yanbu",
            'ar-sa' => 'ينبع'
        ];

        City::create([
            'name' => $name,
        ]);

    }
}