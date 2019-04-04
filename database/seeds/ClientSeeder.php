<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('resellers')->insert([
            'id' => '1',
            'label' => 'Skyline Dynamics',
            'cname' => 'portal',
            'host' => 'portal.skylinedynamics.com'
        ]);

        DB::table('resellers')->insert([
            'id' => '2',
            'label' => 'FAMA Technologies',
            'cname' => 'fama',
            'host' => 'localhost',
            'logo_uri' => 'assets/images/fama-logo.png'
        ]);

         DB::table('clients')->insert([
            'id' => '1',
            'label' => 'Test Client'
        ]);

         DB::table('clients')->insert([
            'id' => '2',
            'label' => 'Food Basics'
        ]);

        DB::table('concepts')->insert([
            'id' => '1',
            'label' => 'Hamburgini',
            'country' => 'SA',
            'dialing_code' => '966',
            'client_id' => 2,
            'default_opening_hours' => '[{"day": 0, "open": "07:00", "closed": "18:00"}, {"day": 1, "open": "07:30", "closed": "23:00"}]',
            'default_menu_id' => 2,
            'default_pos' => 'foodics',
            'default_delivery_charge' => 5,
            'default_promised_time_delta_delivery' => 45,
            'default_promised_time_delta_pickup' => 45,
            'default_minimum_order_amount' => 10,
            'default_driver_location_ttl' => 5
        ]);

        DB::table('concepts')->insert([
            'id' => '2',
            'label' => 'Test Concept',
            'country' => 'SA',
            'dialing_code' => '966',
            'client_id' => 1,
            'default_opening_hours' => '[{"day": 0, "open": "07:00", "closed": "18:00"}, {"day": 1, "open": "07:30", "closed": "23:00"}]',
            'default_menu_id' => 1,
            'default_pos' => 'foodics',
            'default_delivery_charge' => 5,
            'default_promised_time_delta_delivery' => 45,
            'default_promised_time_delta_pickup' => 45,
            'default_minimum_order_amount' => 10,
            'default_driver_location_ttl' => 5
        ]);

        DB::table('integrations')->insert([
            'concept_id' => '1',
            'type' => 'foodics'
        ]);

        DB::table('integration_options')->insert([
            'integration_id' => 1,
            'option_key' => 'api_key',
            'option_value' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcHAiLCJhcHAiOjQzLCJidXMiOm51bGwsImNvbXAiOm51bGwsInNjcnQiOiJYTzVDUkcifQ.WyM9bmJpREsv-YFZpu0GCE_-Zyozh9gqWdKUsX-R-GQ'
        ]);

        DB::table('integration_options')->insert([
            'integration_id' => 1,
            'option_key' => 'business_key',
            'option_value' => '_16718278'
        ]);

        DB::table('integrations')->insert([
            'concept_id' => '2',
            'type' => 'foodics'
        ]);

        DB::table('integration_options')->insert([
            'integration_id' => 2,
            'option_key' => 'api_key',
            'option_value' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcHAiLCJhcHAiOjQzLCJidXMiOm51bGwsImNvbXAiOm51bGwsInNjcnQiOiJYTzVDUkcifQ.WyM9bmJpREsv-YFZpu0GCE_-Zyozh9gqWdKUsX-R-GQ'
        ]);

        DB::table('integration_options')->insert([
            'integration_id' => 2,
            'option_key' => 'business_key',
            'option_value' => '_16718278'
        ]);

        DB::table('providers')->insert([
            'name' => 'email'
        ]);

        DB::table('providers')->insert([
            'name' => 'twitter'
        ]);

        DB::table('providers')->insert([
            'name' => 'facebook'
        ]);

        DB::table('providers')->insert([
            'name' => 'google'
        ]);
    }
}