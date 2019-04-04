<?php
namespace App\Api\V1\Services;

use App\Api\V1\Models\Item;
use App\Api\V1\Models\ItemModifier;
use App\Api\V1\Models\Modifier;
use App\Api\V1\Models\Favorite;
use App\Api\V1\Models\ItemOrder;
use Illuminate\Support\Facades\Request;

class HamburginiMealService
{


    public function calculateMealPrice($input) {
        if (app('cache')->store('file')->has($input)) {
            app('log')->debug('Returning meal price from cache...');
            return app('cache')->store('file')->get($input);
        }

        app('log')->debug('MEAL PRICE REQUEST:'.$input);
        $input = json_decode($input, false);
        $totalPrice = 0;
        $modifiers = [];

        // convert all modifers to real modifiers
        foreach ($input->modifiers as $mod) {
            if (!isset($mod->id)) {
                // Modifier ID is missing from request 
                return -1;
            }
            $modifier = Modifier::find($mod->id);
            $itemModifier = new ItemModifier();
            $itemModifier->modifier = $modifier;
            $itemModifier->quantity = $mod->quantity;
            $itemModifier->price = $mod->price;

            $modifiers[] = $itemModifier;
        }

        $meal = Item::find($input->id);

        $matches = array();
        preg_match('/^http.*\/(\d*)$/', $meal->code, $matches);
        $mealItemId = $matches[1];

        $nameOfSize = $this->getNameOfSize($modifiers);
        $mealItem = $this->getItemAndSizeForMeal($mealItemId, $nameOfSize, $meal);
        $mealItemModifiers = $this->getMealItemModifiers($mealItem->item, $modifiers);
        $fries = $this->getMealCompanionItem('fries', $modifiers, $nameOfSize);
        $beverage = $this->getMealCompanionItem('beverages', $modifiers, $nameOfSize);
        
        $friesModifiers = array();
        if (isset($fries->item)) {
            $friesModifiers = $this->getMealItemModifiers($fries->item, $modifiers);
        }

        $totalPrice = $mealItem->price;

        foreach ($mealItemModifiers as $mealItemModifier) {
            $totalPrice += $mealItemModifier->price * $mealItemModifier->quantity; 
        }
        foreach ($friesModifiers as $friesModifier) {
            $totalPrice += $friesModifier->price * $friesModifier->quantity;
        }

        $totalPrice += isset($fries->price)? $fries->price: 0;
        $totalPrice += isset($beverage->price)? $beverage->price: 0;

        app('log')->debug('TOTAL PRICE: '.$totalPrice);
        app('cache')->store('file')->put(json_encode($input), $totalPrice, 5);
        return $totalPrice;
    }

    /**
     * Returns products (array of products in Foodics order format) and 
     *         price for a meal configuration
     **/
    public function convertMealToProducts($orderItem) {
        $products = [];
        $totalPrice = 0;

        $modifiers = $orderItem->itemOrderModifiers()->get();
        $matches = array();
        preg_match('/^http.*\/(\d*)$/', $orderItem->item->code, $matches);
        $mealItemId = $matches[1];

        $nameOfSize = $this->getNameOfSize($modifiers);
        $mealItem = $this->getItemAndSizeForMeal($mealItemId, $nameOfSize);
        $mealItemModifiers = $this->getMealItemModifiers($mealItem->item, $modifiers);
        $fries = $this->getMealCompanionItem('fries', $modifiers, $nameOfSize);
        $beverage = $this->getMealCompanionItem('beverages', $modifiers, $nameOfSize);

        // Now create foodics products for meal item (burger), beverage and fries...
        if($orderItem->discount != 0.0){
            $discount_percentage = ($orderItem->item->price - $orderItem->discount) / $orderItem->item->price;
        }
        
        // 1. Meal Item (burger)
        $product = new \stdClass();
        $product->product_hid = $mealItem->item->code;
        $product->product_size_hid = $mealItem->size->code;
        $product->original_price = $mealItem->price;
        $product->quantity = $orderItem->quantity;
        $final_price =  $product->original_price * $product->quantity;
        if($orderItem->discount != 0.0){
            if($discount_percentage == 0) {
                $product->discount_amount = $final_price;
                $final_price = 0;
            } else {
                $product->discount_amount = $product->original_price * $discount_percentage;
                $final_price = $final_price - $product->discount_amount;
            }
        }
        $product->final_price = $final_price;

        $options = [];
        foreach ($mealItemModifiers as $mealItemModifier) {
            $option = new \stdClass();
            $option->hid = $mealItemModifier->code;
            $option->quantity = $mealItemModifier->quantity;
            $option->original_price = $mealItemModifier->price;
            $option->final_price = $option->original_price * $option->quantity * $orderItem->quantity;
            $options[] = $option;

            // Add price to product
            $product->final_price += $option->final_price;
        }
        $product->options = $options;

        $remove_options = [];
        $removed_ingredients = $orderItem->itemOrderIngredients()->where('quantity',0)->get();
        if($removed_ingredients || count($removed_ingredients) > 0) {
            foreach($removed_ingredients as $orderIngredient) {
                // create remove ingredients object first
                $option = new \stdClass();
                $option->hid = $orderIngredient->ingredient->ingredient->code;
                $remove_options[] = $option;
            }
        }

        // Set the remove ingredients array object
        $product->removed_ingredients = $remove_options;

        $products[] = $product;
        $totalPrice += $product->final_price;



        // 2. Fries
        $product = new \stdClass();
        $product->product_hid = $fries->item->code;
        $product->product_size_hid = $fries->size->code;
        $product->original_price = $fries->price;
        $product->quantity = $orderItem->quantity;
        $final_price =  $product->original_price * $product->quantity;
        if($orderItem->discount != 0.0){
            if($discount_percentage == 0) {
                $product->discount_amount = $final_price;
                $final_price = 0;
            } else {
                $product->discount_amount = $product->original_price * $discount_percentage;
                $final_price = $final_price - $product->discount_amount;
            }
        }
        $product->final_price = $final_price;

        $options = [];
        foreach ($fries->modifiers as $friesModifier) {
            $option = new \stdClass();
            $option->hid = $friesModifier->code;
            $option->quantity = $friesModifier->quantity;
            $option->original_price = $friesModifier->price;
            $option->final_price = $option->original_price * $option->quantity * $orderItem->quantity;
            $options[] = $option;

            // Add price to product
            $product->final_price += $option->final_price;
        }
        $product->options = $options;
        $products[] = $product;
        $totalPrice += $product->final_price;


        // 3. Beverage
        $product = new \stdClass();
        $product->product_hid = $beverage->item->code;
        $product->product_size_hid = $beverage->size->code;
        $product->original_price = $beverage->price;
        $product->quantity = $orderItem->quantity;
        $final_price =  $product->original_price * $product->quantity;
        if($orderItem->discount != 0.0){
            if($discount_percentage == 0) {
                $product->discount_amount = $final_price;
                $final_price = 0;
            } else {
                $product->discount_amount = $product->original_price * $discount_percentage;
                $final_price = $final_price - $product->discount_amount;
            }
        }
        $product->final_price = $final_price;
        $product->options = [];

        $products[] = $product;
        $totalPrice += $product->final_price;

        $response = new \stdClass();
        $response->products = $products;
        $response->price = $totalPrice;

        return $response;
    }




    private function getNameOfSize($modifiers) {
        foreach ($modifiers as $modifier) {
            if ($modifier->modifier->modifierGroup->code == 'sizes') {
                $translations = $modifier->modifier->translations('name');
                return $translations->in('en-us');
            }
        }
        return 'Regular';  // default, horribly tied to Hamburgini!!  TODO fix this
    }

    private function getItemAndSizeForMeal($mealId, $size) {
        $result = new \stdClass();

        $mealItem = Item::find($mealId);
        $result->item = $mealItem;

        $mealItemSizeModifierGroup = $mealItem->modifierGroups()->where('code', 'sizes')->first();

        $result->size = $mealItemSizeModifierGroup->modifiers()->first();

        // added this code for correction of price calculation
        $result->price = $result->size->price;

        return $result;
    }

    private function getMealItemModifiers($item, $modifiers) {
        $response = array();

        // modified the get meal item modifiers for the changing
        //$itemModifierGroup = $item->modifierGroups()->where('code', 'modifiers')->first();
        $itemModifierGroup = $item->modifierGroups()->where(function($query) {
            $query->orWhere('code','modifiers')
                ->orWhere('code','like','%-modifiers');
        })->first();

        if ($itemModifierGroup) {
            $itemModifiers = $itemModifierGroup->modifiers;
            foreach ($itemModifiers as $itemModifier) {
                // check incoming modifiers for a match
                foreach ($modifiers as $modifier) {
                    if ($modifier->modifier->id == $itemModifier->id) {
                        $itemModifier->quantity = $modifier->quantity;
                        array_push($response, $itemModifier); 
                    }
                }
            }
        }
        return $response;
    }

    private function getMealCompanionItem($type, $modifiers, $size) {
        app('log')->debug('SEARCHING FOR COMPANION: '.$type.' '.$size);
        $response = new \stdClass();

        foreach ($modifiers as $modifier) {
            $mod = Modifier::find($modifier->modifier->id);
            app('log')->debug('MODIFIER ID:'.$modifier->modifier->id.', CODE:'.$mod->modifierGroup->code);
            if ($mod->modifierGroup->code == $type) {
                $itemId = $this->getItemIdFromUri($mod->code);
                app('log')->debug('ITEM ID:'.$itemId);
                if ($itemId > 0) {
                    $item = Item::find($itemId);
                    break;
                }
                else {
                    return $response;
                }
            }
        }

        if (!isset($item)) {
            // type not found
            app('log')->debug('Type '.$type.' not found in request');
            return $response;
        }

        $response->item = $item;
        $response->modifiers = $this->getMealItemModifiers($item, $modifiers);
        
        $sizesModifierGroup = $item->modifierGroups()->where('code', 'sizes')->first();
        foreach ($sizesModifierGroup->modifiers as $modifier) {
            $translations = $modifier->translations('name');
            $thisSize = $translations->in('en-us');
            app('log')->debug('Passed in size: '.$size.'. This size: '.$thisSize);

            if ($thisSize == 'Regular' || $thisSize == 'Regular 16oz') {
                $default = $modifier;
                if ($size == 'Regular') {
                    $response->size = $modifier;
                    $response->price = $modifier->price;
                }
            }
            elseif ($thisSize == 'UP Size' || $thisSize == 'Large 24oz') {
                if ($size == 'UP Size') {
                    $response->size = $modifier;
                    $response->price = $modifier->price;
                }                
            }
        }

        if (!isset($response->size)) {
            // Get Regular size
            $response->size = $default;
            $response->price = $default->price;
        }

        return $response;

    }

    private function getItemIdFromUri($uri) {
        $matches = array();
        if (preg_match('/^http.*\/(\d*)$/', $uri, $matches)) {
            return $matches[1];
        }
        return -1;
    }

    public function getCorrectPrice(Favorite $favorite)
    {
        // set price to 0
        $price = 0;
        $sizeModifier = 0;
        if(Request::header('Solo-Concept') != 8) {
            // check favorite item if meal
            $item = Item::find($favorite->item_id);
            $category = $item->category()->first();
            if($category and strtolower($category->translate('en-us')->name) == 'meals') {
                $price = $favorite->price;
                $sizeModifier++;
            } else {
                $modifiers = $favorite->modifiers()->get();
                foreach($modifiers as $favMods) {
                    $mod = Modifier::find($favMods->modifier_id);
                    if($mod->modifierGroup()->where('code','LIKE','%size%')->first()) {
                        $sizeModifier++;
                    }
                    $price += $mod->price * $favMods->quantity;
                }
            }

            // w/o modifier group
            if($sizeModifier == 0){
                $price += $favorite->price;
            }
        } else {
            $price = $favorite->price;
        }

        return $price;
    }

    /*public function getFavNumberOfSizeMods(Favorite $favorite)
    {
        $count = 0;

        // get each item for the favorites
        $item = Item::find($favorite->item_id);

        // get the modifier group with code size
        $modGroup = $item->modifierGroups()->where('code','LIKE','%size%')->first();

        if($modGroup) {
            // get the number of modifiers for the size mod group
            $count = $modGroup->modifiers()->where('enabled',1)->count();
        }

        return $count;
    }

    public function getOrderNumberOfSizeMods(ItemOrder $item_order){

        $count = 0;

        // get each item for the favorites
        $item = Item::find($item_order->item_id);

        // get the modifier group with code size
        $modGroup = $item->modifierGroups()->where('code','LIKE','%size%')->first();

        if($modGroup) {
            // get the number of modifiers for the size mod group
            $count = $modGroup->modifiers()->where('enabled',1)->count();
        }


        return $count;
    }*/

    public function getNumberOfSizeMods($object){

        $count = 0;

        // get each item for the favorites
        $item = Item::find($object->item_id);

        // get the modifier group with code size
        $modGroup = $item->modifierGroups()->where('code','LIKE','%size%')->first();

        if($modGroup) {
            // get the number of modifiers for the size mod group
            $count = $modGroup->modifiers()->where('enabled',1)->count();
        }


        return $count;
    }

}
