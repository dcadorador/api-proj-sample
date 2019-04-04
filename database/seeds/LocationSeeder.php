<?php

use App\Api\V1\Models\Location;  
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $name = [
            'en-us' => 'Popeyes Othaim Mall - Hassa',
            'ar-sa' => 'ببيص عظيم مال - حاسة'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 25.399882,
            'long' => 49.57785,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Hassa Mall – Hassa',
            'ar-sa' => 'بوبايز حسا مول - حسا'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 25.328516,
            'long' => 49.550354,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Panorama RYD',
            'ar-sa' => 'بوبايز بانوراما ريد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.692829,
            'long' => 46.668723,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Andalusia RYD',
            'ar-sa' => 'بوبايس الأندلس ريد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.762607,
            'long' => 46.81042,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes MOJ Jeddah',
            'ar-sa' => 'بوبايز وزارة العدل جدة'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 21.762681,
            'long' => 39.11458,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Hamdaniyah JED',
            'ar-sa' => 'بوبايز الحمدانية جد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 21.741926,
            'long' => 39.191505,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Dove Riyadh',
            'ar-sa' => 'بوبايز حمامة الرياض'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.679508,
            'long' => 46.654122,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Hayat Mall RYD',
            'ar-sa' => 'بوبايز حياة مول ريد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.743139,
            'long' => 46.68009,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Jubail Centro',
            'ar-sa' => 'بوبايز مركز الجبيل'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 27.001393,
            'long' => 46.68009,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Red Sea Mall JED',
            'ar-sa' => 'بوبايز رد سي مول جد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 21.627735,
            'long' => 39.110861,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'P36 - Popeyes Othaim RUD',
            'ar-sa' => 'P36 - بوبايز العثيم رود'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.68543,
            'long' => 46.775127,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Sulimnaia RYD',
            'ar-sa' => 'بوبايز سوليمنيا ريد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.708343,
            'long' => 46.687778,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Popeyes Tahlia JED',
            'ar-sa' => 'بوبايز تحلية جد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 21.554377,
            'long' => 39.169224,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);

        $name = [
            'en-us' => 'Rashid Mall KHB',
            'ar-sa' => 'رشيد مال خب'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 26.290886,
            'long' => 50.18068,
            'pos' => '',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => '8'
        ]);



        /*$name = [
            'en-us' => 'Dareen',
            'ar-sa' => 'درين'
        ];

        Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.5946731,
            'long' => 46.6036507,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": 1, "open":"04:00", "closed":"02:00"}]',
            'concept_id' => '2'
        ]);


        $name = [
            'en-us' => 'Malik Fahd',
            'ar-sa' => 'ملك فهد'
        ];

        $location = Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.6393153,
            'long' => 46.6733306,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[]',
            'concept_id' => '1'
        ]);

        $name = [
            'en-us' => 'Exit 24',
            'ar-sa' => 'ممخرج 24'
        ];

        $location = Location::create([
            'status' => 'active',
            'name' => $name,
            'country' => 'SA',
            'lat' => 24.7117,
            'long' => 46.7242,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[]',
            'concept_id' => '2'
        ]);

        DB::table('devices')->insert([
            'label' => 'Singles Kiosk 1',
            'location_id' => 1,
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ]);
        
        DB::table('devices')->insert([
            'label' => 'Singles Kiosk 2',
            'location_id' => 1,
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ]);*/
    }
}