<?php

use App\Api\V1\Models\Menu;
use App\Api\V1\Models\Role;
use App\Api\V1\Models\Category;  
use App\Api\V1\Models\BundledCategory;  
use App\Api\V1\Models\BundledItem;  
use App\Api\V1\Models\Item;  
use App\Api\V1\Models\Ingredient;
use App\Api\V1\Models\ItemIngredient;    
use App\Api\V1\Models\CustomField;
use App\Api\V1\Models\CustomFieldData;       
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $name = [
            'en-us' => 'administrator',
            'ar-sa' => 'مدير'
        ];

        Role::create([
            'label' => 'administrator',
            'name' => $name
        ]);

        $name = [
            'en-us' => 'restaurant',
            'ar-sa' => 'مطعم'
        ];

        Role::create([
            'label' => 'restaurant',
            'name' => $name
        ]);

        $name = [
            'en-us' => 'driver',
            'ar-sa' => 'سائق'
        ];

        Role::create([
            'label' => 'driver',
            'name' => $name
        ]);

        $name = [
            'en-us' => 'call center',
            'ar-sa' => 'مركز الاتصال'
        ];

        Role::create([
            'label' => 'call center',
            'name' => $name
        ]);

        $name = [
            'en-us' => 'supervisor',
            'ar-sa' => 'مشرف'
        ];

        Role::create([
            'label' => 'supervisor',
            'name' => $name
        ]);


        Menu::create([
            'label' => 'Test Menu',
            'concept_id' => '2'
        ]);

        $name = [
            'en-us' => 'Meals',
            'ar-sa' => 'درين'
        ];
        $description = [
            'en-us' => 'Burger, chips and drink',
            'ar-sa' => 'درين'
        ];

        Category::create([
            'menu_id' => 1,
            'display_order' => 1,
            'name' => $name,
            'description' => $description,
            'image_uri' => 'assets/images/meals.jpg',

        ]);

        $name = [
            'en-us' => 'Desserts',
            'ar-sa' => 'Desserts AR'
        ];
        $description = [
            'en-us' => 'Delicious Sweets',
            'ar-sa' => 'Delicious Sweets AR'
        ];

        CustomField::create([
            'concept_id' => 2,
            'label' => 'icon-on',
            'type' => 'url']);

        CustomField::create([
            'concept_id' => 2,
            'label' => 'icon-off',
            'type' => 'url']);

        CustomFieldData::create([
            'custom_field_id' => 1,
            'data' => 'burger-on.png',
            'custom_fieldable_type' => 'App\Api\V1\Models\Category',
            'custom_fieldable_id' => 1]);

        CustomFieldData::create([
            'custom_field_id' => 2,
            'data' => 'burger-off.png',
            'custom_fieldable_type' => 'App\Api\V1\Models\Category',
            'custom_fieldable_id' => 1]);

        Category::create([
            'menu_id' => 1,
            'display_order' => 2,
            'name' => $name,
            'description' => $description,
            'image_uri' => 'assets/images/desserts.jpg',

        ]);

        $name = [
            'en-us' => 'Double Herfy Combo',
            'ar-sa' => 'درين'
        ];
        $description = [
            'en-us' => 'It\'s a Double Herfy.  In a meal configuration',
            'ar-sa' => 'درين'
        ];

        Item::create([
            'category_id' => 1,
            'display_order' => 1,
            'name' => $name,
            'description' => $description,
            'image_uri' => 'assets/images/Double Herfy Combo.jpg',
            'price' => 20,
            'in_stock' => true
        ]);

        $name = [
            'en-us' => 'Super Chicken Combo',
            'ar-sa' => 'درين'
        ];
        $description = [
            'en-us' => 'It\'s a Chicken Burger, with fries and drink',
            'ar-sa' => 'درين'
        ];

        Item::create([
            'category_id' => 1,
            'display_order' => 1,
            'name' => $name,
            'description' => $description,
            'image_uri' => 'assets/images/Super Chicken Combo.jpg',
            'price' => 20,
            'in_stock' => true
        ]);

        Ingredient::create([
            'code' => '_97d4a257',
            'name' => [
                'en-us' => 'Tomato',
                'ar-sa' => 'طماطم'
            ],
            'price' => 0
        ]);

        ItemIngredient::create([
            'item_id' => 1,
            'ingredient_id' => 1,
            'quantity' => 1
        ]);
        
        BundledItem::create([
            'parent_item_id' => 1,
            'primary_item_id' => 2
        ]);

        BundledCategory::create([
            'bundled_item_id' => 1,
            'category_id' => 1,
            'default_item_id' => 2
        ]);
    }
}