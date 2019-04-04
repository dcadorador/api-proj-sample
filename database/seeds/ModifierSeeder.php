<?php

use App\Api\V1\Models\ModifierGroup;  
use App\Api\V1\Models\Modifier;  
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ModifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $name = [
            'en-us' => 'Drinks',
            'ar-sa' => 'Al-Drinkaat'
        ];

        ModifierGroup::create([
            'concept_id' => '1',
            'display_order' => 1,
            'name' => $name,
            'image_uri' => null,
            'minimum' => 1,
            'maximum' => 1
        ]);

        $name = [
            'en-us' => 'Pepsi',
            'ar-sa' => 'Bebsi'
        ];

        Modifier::create([
            'modifier_group_id' => 1,
            'display_order' => 1,
            'name' => $name,
            'image_uri' => null,
            'price' => 0
        ]);

        $name = [
            'en-us' => '7-Up',
            'ar-sa' => 'Sabah-Fowk'
        ];

        Modifier::create([
            'modifier_group_id' => 1,
            'display_order' => 1,
            'name' => $name,
            'image_uri' => null,
            'price' => 0
        ]);

        DB::table('item_modifier_group')->insert([
            'modifier_group_id' => 1,
            'item_id' => 1
        ]);

        DB::table('item_modifier_group')->insert([
            'modifier_group_id' => 1,
            'item_id' => 2
        ]);


    }
}