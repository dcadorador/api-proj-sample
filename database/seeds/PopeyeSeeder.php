<?php

use App\Api\V1\Models\ApiResponse;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Models\Menu;
use App\Api\V1\Models\Category;
use App\Api\V1\Models\Item;
use App\Api\V1\Models\Location;
use App\Api\V1\Models\Concept;


class PopeyeSeeder extends Seeder {

    public function run() {

        $concept = Concept::where('label','Popeyes')->first();
        /*$name = [
            'en-us' => 'Chicken Combo',
            'ar-sa' => 'الدجاجة كومبو'
        ];

        $cat1 = Category::create([
            'menu_id' => 10,
            'display_order' => 1,
            'code' => 10000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => '2Pc Bonafide Combo',
            'ar-sa' => '2Pc Bonafide Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10001,
            'name' => $item,
            'description' => $item,
            'price' => 15,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3Pc Bonafide Combo',
            'ar-sa' => '3Pc Bonafide Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10002,
            'name' => $item,
            'description' => $item,
            'price' => 18,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '4Pc Bonafide Combo',
            'ar-sa' => '4Pc Bonafide Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10003,
            'name' => $item,
            'description' => $item,
            'price' => 22,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '8Pc Family Meal',
            'ar-sa' => '8Pc Family Meal',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10004,
            'name' => $item,
            'description' => $item,
            'price' => 45,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '12Pc Family Meal',
            'ar-sa' => '12Pc Family Meal',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10005,
            'name' => $item,
            'description' => $item,
            'price' => 58,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '16Pc Family Meal',
            'ar-sa' => '16Pc Family Meal',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10006,
            'name' => $item,
            'description' => $item,
            'price' => 75,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '20Pc Family Meal',
            'ar-sa' => '20Pc Family Meal',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10007,
            'name' => $item,
            'description' => $item,
            'price' => 85,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3Pc C-Tenders Combo',
            'ar-sa' => '3Pc C-Tenders Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10008,
            'name' => $item,
            'description' => $item,
            'price' => 17,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '5Pc C-Tenders Combo',
            'ar-sa' => '5Pc C-Tenders Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10009,
            'name' => $item,
            'description' => $item,
            'price' => 22,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '12Pc Nuggets Meal',
            'ar-sa' => '12Pc Nuggets Meal',
        ];

        $newItem=Item::create([
            'category_id' => $cat1->id,
            'code' => 10010,
            'name' => $item,
            'description' => $item,
            'price' => 23,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $name = [
            'en-us' => 'Chicken',
            'ar-sa' => 'دجاجة'
        ];

        $cat2 = Category::create([
            'menu_id' => 10,
            'display_order' => 2,
            'code' => 11000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => '2Pc Chicken Snacks',
            'ar-sa' => '2Pc Chicken Snacks',
        ];

        $newItem=Item::create([
            'category_id' => $cat2->id,
            'code' => 11001,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3Pc Chicken Snacks',
            'ar-sa' => '3Pc Chicken Snacks',
        ];

        $newItem=Item::create([
            'category_id' => $cat2->id,
            'code' => 11002,
            'name' => $item,
            'description' => $item,
            'price' => 14,
            'in_stock' => 1,
            'enabled' => 1
        ]);


        $item = [
            'en-us' => '3pc Chicken Tenders Only',
            'ar-sa' => '3pc Chicken Tenders Only',
        ];

        $newItem=Item::create([
            'category_id' => $cat2->id,
            'code' => 11006,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Chicken Deluxe Sandwich',
            'ar-sa' => 'Chicken Deluxe Sandwich',
        ];

        $newItem=Item::create([
            'category_id' => $cat2->id,
            'code' => 11010,
            'name' => $item,
            'description' => $item,
            'price' => 11,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3 pc Chicken Tenders',
            'ar-sa' => '3 pc Chicken Tenders',
        ];

        $newItem=Item::create([
            'category_id' => $cat2->id,
            'code' => 11011,
            'name' => $item,
            'description' => $item,
            'price' => 11,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $name = [
            'en-us' => 'Seafood Combo',
            'ar-sa' => 'الأطعمة البحرية، كومبو'
        ];

        $cat4 = Category::create([
            'menu_id' => 10,
            'display_order' => 3,
            'code' => 20000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => 'Fish Sandwich Combo',
            'ar-sa' => 'Fish Sandwich Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat4->id,
            'code' => 20001,
            'name' => $item,
            'description' => $item,
            'price' => 18,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Shrimp Sandwich Combo',
            'ar-sa' => 'Shrimp Sandwich Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat4->id,
            'code' => 20002,
            'name' => $item,
            'description' => $item,
            'price' => 19,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Fish Fillet Combo',
            'ar-sa' => 'Fish Fillet Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat4->id,
            'code' => 20003,
            'name' => $item,
            'description' => $item,
            'price' => 29,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Fish & B-Shrimp Combo',
            'ar-sa' => 'Fish & B-Shrimp Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat4->id,
            'code' => 20005,
            'name' => $item,
            'description' => $item,
            'price' => 32,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'B-Shrimp Combo',
            'ar-sa' => 'B-Shrimp Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat4->id,
            'code' => 20006,
            'name' => $item,
            'description' => $item,
            'price' => 33,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $name = [
            'en-us' => 'Seafood',
            'ar-sa' => 'مأكولات بحرية'
        ];

        $cat5 = Category::create([
            'menu_id' => 10,
            'display_order' => 4,
            'code' => 21000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => 'Fish Sandwich',
            'ar-sa' => 'Fish Sandwich',
        ];

        $newItem=Item::create([
            'category_id' => $cat5->id,
            'code' => 21001,
            'name' => $item,
            'description' => $item,
            'price' => 12,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Shrimp Sandwich',
            'ar-sa' => 'Shrimp Sandwich',
        ];

        $newItem=Item::create([
            'category_id' => $cat5->id,
            'code' => 21002,
            'name' => $item,
            'description' => $item,
            'price' => 13,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Fish Strips Platter',
            'ar-sa' => 'Fish Strips Platter',
        ];

        $newItem=Item::create([
            'category_id' => $cat5->id,
            'code' => 21003,
            'name' => $item,
            'description' => $item,
            'price' => 26,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Fish & Butterfly Shrimp Platter',
            'ar-sa' => 'Fish & Butterfly Shrimp Platter',
        ];

        $newItem=Item::create([
            'category_id' => $cat5->id,
            'code' => 21005,
            'name' => $item,
            'description' => $item,
            'price' => 30,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Butterfly Shrimp Platter',
            'ar-sa' => 'Butterfly Shrimp Platter',
        ];

        $newItem=Item::create([
            'category_id' => $cat5->id,
            'code' => 21006,
            'name' => $item,
            'description' => $item,
            'price' => 30,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $name = [
            'en-us' => 'Side Item',
            'ar-sa' => 'الجانب البند'
        ];

        $cat6 = Category::create([
            'menu_id' => 10,
            'display_order' => 5,
            'code' => 30000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => 'Small MPG',
            'ar-sa' => 'Small MPG',
        ];

        $newItem=Item::create([
            'category_id' => $cat6->id,
            'code' => 30001,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Medium MPG',
            'ar-sa' => 'Medium MPG',
        ];

        $newItem=Item::create([
            'category_id' => $cat6->id,
            'code' => 30002,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small Coleslaw',
            'ar-sa' => 'Small Coleslaw',
        ];

        $newItem=Item::create([
            'category_id' => $cat6->id,
            'code' => 30003,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large Coleslaw',
            'ar-sa' => 'Large Coleslaw',
        ];

        $newItem=Item::create([
            'category_id' => $cat6->id,
            'code' => 30004,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small Veggie Rice',
            'ar-sa' => 'Small Veggie Rice',
        ];

        $newItem=Item::create([
            'category_id' => $cat6->id,
            'code' => 30007,
            'name' => $item,
            'description' => $item,
            'price' => 6,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large Veggie Rice',
            'ar-sa' => 'Large Veggie Rice',
        ];

        $newItem=Item::create([
            'category_id' => $cat6->id,
            'code' => 30008,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $name = [
            'en-us' => 'Biscuit',
            'ar-sa' => 'بسكويت'
        ];

        $cat7 = Category::create([
            'menu_id' => 10,
            'display_order' => 6,
            'code' => 40000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => '1Pc Biscuit',
            'ar-sa' => '1Pc Biscuit',
        ];

        $newItem=Item::create([
            'category_id' => $cat7->id,
            'code' => 40001,
            'name' => $item,
            'description' => $item,
            'price' => 1,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '6Pc Biscuit',
            'ar-sa' => '6Pc Biscuit',
        ];

        $newItem=Item::create([
            'category_id' => $cat7->id,
            'code' => 40002,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '12Pc Biscuit',
            'ar-sa' => '12Pc Biscuit',
        ];

        $newItem=Item::create([
            'category_id' => $cat7->id,
            'code' => 40003,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $name = [
            'en-us' => 'Promo',
            'ar-sa' => 'الترويجي'
        ];

        $cat8 = Category::create([
            'menu_id' => 10,
            'display_order' => 7,
            'code' => 80000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => 'Tackle Box',
            'ar-sa' => 'Tackle Box',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80001,
            'name' => $item,
            'description' => $item,
            'price' => 25,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3pc Chicken LTO',
            'ar-sa' => '3pc Chicken LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80002,
            'name' => $item,
            'description' => $item,
            'price' => 12,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3pc Tender LTO',
            'ar-sa' => '3pc Tender LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80003,
            'name' => $item,
            'description' => $item,
            'price' => 12,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3pc Fish LTO',
            'ar-sa' => '3pc Fish LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80004,
            'name' => $item,
            'description' => $item,
            'price' => 12,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Apple Pie',
            'ar-sa' => 'Apple Pie',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80005,
            'name' => $item,
            'description' => $item,
            'price' => 6,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Spicy Box',
            'ar-sa' => 'Spicy Box',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80006,
            'name' => $item,
            'description' => $item,
            'price' => 20,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '2Pc Bonafide',
            'ar-sa' => '2Pc Bonafide',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80007,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '2Pc Tender Combo',
            'ar-sa' => '2Pc Tender Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80008,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'C-Cajen CMB LTO',
            'ar-sa' => 'C-Cajen CMB LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80009,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Chk-Wrap CMB Lt',
            'ar-sa' => 'Chk-Wrap CMB Lt',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80010,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'C-Creole CMB L',
            'ar-sa' => 'C-Creole CMB L',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80011,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Kabsa Tenders',
            'ar-sa' => 'Kabsa Tenders',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 10028,
            'name' => $item,
            'description' => $item,
            'price' => 19,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Kabsa Chicken',
            'ar-sa' => 'Kabsa Chicken',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 10029,
            'name' => $item,
            'description' => $item,
            'price' => 19,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Ice Tea',
            'ar-sa' => 'Ice Tea',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 50022,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Tropicana',
            'ar-sa' => 'Tropicana',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 50023,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '12 Pc Bonafide',
            'ar-sa' => '12 Pc Bonafide',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80012,
            'name' => $item,
            'description' => $item,
            'price' => 45,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => '3 Pc Bonafide',
            'ar-sa' => '3 Pc Bonafide',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80013,
            'name' => $item,
            'description' => $item,
            'price' => 12,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Ghost Sandwich',
            'ar-sa' => 'Ghost Sandwich',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 11016,
            'name' => $item,
            'description' => $item,
            'price' => 11,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Ghost Sandwich Combo',
            'ar-sa' => 'Ghost Sandwich Combo',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 10030,
            'name' => $item,
            'description' => $item,
            'price' => 18,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Fish Sandwich Combo-LTO',
            'ar-sa' => 'Fish Sandwich Combo-LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80014,
            'name' => $item,
            'description' => $item,
            'price' => 10,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Cajun SW LTO',
            'ar-sa' => 'Cajun SW LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80015,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Creole SW LTO',
            'ar-sa' => 'Creole SW LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80016,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Weap SW LTO',
            'ar-sa' => 'Weap SW LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80017,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Fish SW LTO',
            'ar-sa' => 'Fish SW LTO',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80018,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'MOROCCAN',
            'ar-sa' => 'MOROCCAN',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80019,
            'name' => $item,
            'description' => $item,
            'price' => 22,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'PERI PERI CHICKEN',
            'ar-sa' => 'PERI PERI CHICKEN',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80020,
            'name' => $item,
            'description' => $item,
            'price' => 15,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Ramdan Meal',
            'ar-sa' => 'Ramdan Meal',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80021,
            'name' => $item,
            'description' => $item,
            'price' => 18,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Cajun Sufr & Turf',
            'ar-sa' => 'Cajun Sufr & Turf',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80022,
            'name' => $item,
            'description' => $item,
            'price' => 22,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Family Treat Meal',
            'ar-sa' => 'Family Treat Meal',
        ];

        $newItem=Item::create([
            'category_id' => $cat8->id,
            'code' => 80023,
            'name' => $item,
            'description' => $item,
            'price' => 75,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $name = [
            'en-us' => 'Drinks',
            'ar-sa' => 'مشروبات'
        ];

        $cat9 = Category::create([
            'menu_id' => 10,
            'display_order' => 8,
            'code' => 50000,
            'name' => $name,
            'description' => $name,
        ]);

        $item = [
            'en-us' => 'Medium coca',
            'ar-sa' => 'Medium coca',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50001,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small coca',
            'ar-sa' => 'Small coca',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50002,
            'name' => $item,
            'description' => $item,
            'price' => 3,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large coca',
            'ar-sa' => 'Large coca',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50003,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Medium Diet coca',
            'ar-sa' => 'Medium Diet coca',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50004,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small Diet coca',
            'ar-sa' => 'Small Diet coca',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50005,
            'name' => $item,
            'description' => $item,
            'price' => 3,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large Diet coca',
            'ar-sa' => 'Large Diet coca',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50006,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Medium M-Fanta',
            'ar-sa' => 'Medium M-Fanta',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50007,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small M-Fanta',
            'ar-sa' => 'Small M-Fanta',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50008,
            'name' => $item,
            'description' => $item,
            'price' => 3,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large M-Fanta',
            'ar-sa' => 'Large M-Fanta',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50009,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Medium F-Strawberry',
            'ar-sa' => 'Medium F-Strawberry',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50010,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small F-Strawberry',
            'ar-sa' => 'Small F-Strawberry',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50011,
            'name' => $item,
            'description' => $item,
            'price' => 3,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'LargeF-Strawberry',
            'ar-sa' => 'LargeF-Strawberry',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50012,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Medium Sprite',
            'ar-sa' => 'Medium Sprite',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50013,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small Sprite',
            'ar-sa' => 'Small Sprite',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50014,
            'name' => $item,
            'description' => $item,
            'price' => 3,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large Sprite',
            'ar-sa' => 'Large Sprite',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50015,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Medium M-Dew',
            'ar-sa' => 'Medium M-Dew',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50016,
            'name' => $item,
            'description' => $item,
            'price' => 4,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Small M-Dew',
            'ar-sa' => 'Small M-Dew',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50017,
            'name' => $item,
            'description' => $item,
            'price' => 3,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large M-Dew',
            'ar-sa' => 'Large M-Dew',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50018,
            'name' => $item,
            'description' => $item,
            'price' => 5,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Large Water',
            'ar-sa' => 'Large Water',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50019,
            'name' => $item,
            'description' => $item,
            'price' => 2,
            'in_stock' => 1,
            'enabled' => 1
        ]);

        $item = [
            'en-us' => 'Juice',
            'ar-sa' => 'Juice',
        ];

        $newItem=Item::create([
            'category_id' => $cat9->id,
            'code' => 50021,
            'name' => $item,
            'description' => $item,
            'price' => 2,
            'in_stock' => 1,
            'enabled' => 1
        ]);*/



        $loc = [
            'en-us' => 'Popeyes Dhahran Mall',
            'ar-sa' => 'بوبايز الظهران مول'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '10140',
            'lat' => 26.305607,
            'long' => 50.169769,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Shatea Mall',
            'ar-sa' => 'بوبايز الشاطئ مول'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '10345',
            'lat' => 26.45407,
            'long' => 50.120104,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Ibn Khaldoun Plaza',
            'ar-sa' => 'بوبايز ابن خلدون بلازا'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '10514',
            'lat' => 26.413359,
            'long' => 50.071511,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Al Rakah',
            'ar-sa' => 'بوبايز الراكة'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '10678',
            'lat' => 26.328045,
            'long' => 50.213303,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Qatif City Mall',
            'ar-sa' => 'بوبايز القطيف سيتي مول'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '10716',
            'lat' => 26.557482,
            'long' => 50.037836,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Azizyah',
            'ar-sa' => 'بوبايز العزيزية'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '10871',
            'lat' => 26.2011993,
            'long' => 50.19438163,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Hassa Mall',
            'ar-sa' => 'بوبايز الاحساء مول'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => '11007',
            'lat' => 25.328516,
            'long' => 49.550354,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Othaim Mall',
            'ar-sa' => 'بوبايز العثيم مول'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '11097',
            'lat' => 26.400016,
            'long' => 50.11712,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Corniche',
            'ar-sa' => 'بوبايز الكورنيش'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '11464',
            'lat' => 26.285195,
            'long' => 50.218855,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes King Faisal University',
            'ar-sa' => 'بوبايز جامعة الملك فيصل'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '11604',
            'lat' => 25.352937,
            'long' => 49.591829,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Doha',
            'ar-sa' => 'بوبايز الدوحة'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '11902',
            'lat' => 26.32349654,
            'long' => 50.16522045,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Recreation Center',
            'ar-sa' => 'بوبايز مركز الترفيه'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '11959',
            'lat' => 26.307148,
            'long' => 50.220217,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Uhud',
            'ar-sa' => 'بوبايز احد'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12064',
            'lat' => 26.426957,
            'long' => 50.035,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Enoc',
            'ar-sa' => 'بوبايز اينوك'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12065',
            'lat' => 26.689719,
            'long' => 49.908281,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Prince Turki',
            'ar-sa' => 'بوبايز الامير تركي'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12066',
            'lat' => 24.747065,
            'long' => 46.61795,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Rekal',
            'ar-sa' => 'بوبايز ريكال'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12067',
            'lat' => 24.824592,
            'long' => 46.65883,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Panorama',
            'ar-sa' => 'بوبايز بانوراما'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12155',
            'lat' => 24.692829,
            'long' => 46.668723,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Andalusia',
            'ar-sa' => 'بوبايز الاندلسية'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12340',
            'lat' => 24.762607,
            'long' => 46.81042,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Moj Plaza',
            'ar-sa' => 'بوبايز موج بلازا'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12341',
            'lat' => 21.762681,
            'long' => 39.11458,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Hamdaniyah',
            'ar-sa' => 'بوبايز الحمدانية'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12387',
            'lat' => 21.741926,
            'long' => 39.191505,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Dove',
            'ar-sa' => 'بوبايز دووف'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12497',
            'lat' => 24.679508,
            'long' => 46.654122,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Sawary',
            'ar-sa' => 'بوبايز سواري'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12500',
            'lat' => 24.816045,
            'long' => 46.878,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Hayat Mall',
            'ar-sa' => 'بوبايز الحياه مول'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => '12241',
            'lat' => 24.743139,
            'long' => 46.68009,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Jubail Centro',
            'ar-sa' => 'بوبايز الجبيل'
        ];

        Location::create([
            'status' => 'active',
            'name' => $loc,
            'country' => 'SA',
            'code' => 'P31',
            'lat' => 27.001393,
            'long' => 49.65981,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Red Sea Mall',
            'ar-sa' => 'بوبايز مول البحر الاحمر'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => 'P34',
            'lat' => 21.627735,
            'long' => 39.110861,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Othaim',
            'ar-sa' => 'بوبايز العثيم'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => 'P36',
            'lat' => 24.68543,
            'long' => 46.775127,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);

        $loc = [
            'en-us' => 'Popeyes Sulimnaia',
            'ar-sa' => 'بوبايز السليمانية'
        ];

        Location::create([
            'status' => 'inactive',
            'name' => $loc,
            'country' => 'SA',
            'code' => 'P36',
            'lat' => 24.708343,
            'long' => 46.687778,
            'pos' => 'aloha',
            'delivery_charge' => 5,
            'opening_hours' => '[{"day": "0", "open": "09:00", "closed": "02:00"},{"day": "1", "open": "09:00", "closed": "02:00"},{"day": "2", "open": "09:00", "closed": "02:00"},{"day": "3", "open": "09:00", "closed": "02:00"},{"day": "4", "open": "09:00", "closed": "02:00"},{"day": "5", "open": "09:00", "closed": "02:00"},{"day": "6", "open": "09:00", "closed": "02:00"}]',
            'concept_id' => $concept->id
        ]);


    }

}