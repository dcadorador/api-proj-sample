<?php
namespace App\Api\V1\Services;

use App\Api\V1\Models\CustomerAddress;
use App\Api\V1\Models\Item;
use App\Api\V1\Models\Concept;
use App\Api\V1\Models\Order;

use Carbon\Carbon;

class FoodicsOrderService
{
    public function order(Order $order)
    {
        // get location object & delivery hid
        $location = $order->location()->first();
        $location_delivery_hid = null;
        foreach ($location->customFields as $customField) {
           $location_delivery_hid = $customField->data;
        }

        // added to get concept
        $concept = Concept::find($order->concept_id);
        // get customer object
        $customer = $order->customer()->first();
        // get customer address object
        $customer_address = $order->customerAddress;

        switch($order->type){
            case 'deliver':
                $type = 4;
                break;
            case 'to-go':
            case 'eat-in':
            case 'pickup':
                $type = 3;
                break;
            default:
                $type = 4;
                break;
        }

        // format data
        $foodics = new \stdClass();
        //$foodics->price = $order->subtotal;
        $foodics->price = $order->total;
        $foodics->total_tax = 0;
        $foodics->delivery_price = $order->delivery_charge; // todo change this with the delivery price
        $foodics->discount_amount = $order->discount == 0 ? 0 : $order->discount;
        $foodics->final_price = ($order->discount != 0 and $order->discount < $order->total) ? (int)$order->total - (int)$order->discount : $order->total;

        // todo: HARDCODED the discount HID in FOODICS for burgerizzr only
        if($concept->id == 5 and $order->discount != 0) {
            app('log')->debug('DISCOUNT: '.json_encode($order->discount));
            $foodics->discount_hid = '_67186697';
        }

        // todo added temp : should be the correct code in production $location->cod
        switch($concept->id) {
            case 1:
                //$foodics->branch_hid = '_g3753g79';
                $foodics->branch_hid = $location->code;
                break;
            case 5:
                //$foodics->branch_hid = '_6d742a73';
                $foodics->branch_hid = $location->code;
                break;
            default:
                $foodics->branch_hid = $location->code;
                break;
        }

        $promised_time = is_a($order->promised_time,'DateTime') ? $order->promised_time->format('Y-m-d H:i:s') : $order->promised_time;
        $foodics->due_time = $order->scheduled_time || $order->scheduled_time != '' ? Carbon::parse($order->scheduled_time)->setTimezone('GMT+3')->toDateTimeString() : Carbon::parse($promised_time)->setTimezone('GMT+3')->toDateTimeString();
        $foodics->type = $type;
        if($type == 4) {
            $foodics->delivery_address = new \stdClass();
            $foodics->delivery_address->address = str_replace(null,'',preg_replace('/\s+/', ' ',$customer_address->line1.' '.$customer_address->line1));
            $foodics->delivery_address->latitude = $customer_address->lat;
            $foodics->delivery_address->longitude = $customer_address->long;
            $foodics->delivery_address->notes = $customer_address->label;
            // should add the delivery zone hid for the location
            // todo change to the correct delivery zone $location_delivery_hid
            switch($concept->id) {
                case 1:
                    //$foodics->delivery_address->delivery_zone_hid = '_8697d87g';
                    $foodics->delivery_address->delivery_zone_hid = $location_delivery_hid ? $location_delivery_hid : '_8697d87g';
                    break;
                case 5:
                    //$foodics->delivery_address->delivery_zone_hid = '_g3759697';
                    $foodics->delivery_address->delivery_zone_hid = $location_delivery_hid ? $location_delivery_hid : '_g3759697';
                    break;
                default:
                    $foodics->delivery_address->delivery_zone_hid = $location_delivery_hid;
                    break;
            }
        }
        //
        //$foodics->tags = [];
        switch(strtolower($order->source)) {
            case 'android':
                $foodics->tags = ['Android-Order'];
                break;
            case 'ios':
                $foodics->tags = ['iOS-Order'];
                break;
            case 'web':
                $foodics->tags = ['Web-Order'];
                break;
            default:
                $foodics->tags = [];
                break;
        }
        //
        $foodics->customer = new \stdClass();
        $foodics->customer->name = preg_replace('/\s+/', ' ',$customer->first_name.' '.$customer->last_name);
        $foodics->customer->email = $customer->email;
        $foodics->customer->phone = !is_null($customer->mobile) ? preg_replace('/^966/', '', $customer->mobile) : '501234567';
        $foodics->customer->country_code = $concept->country;
        $foodics->taxes = [];

        switch($concept->id) {
            case 1:
                $payment_hid = '_67a2d3g7';
                break;
            case 5:
                $payment_hid = '_2735gd47';
                break;
            default:
                $payment_hid = '_16714278';
                break;
        }

        // update payments foodics api service
        // see Hamza email: FOODICS API UPDATES
        if($order->payment_type == 'card') {
            $foodics->payments = [];
            $paymentClass = new \stdClass();
            $paymentClass->amount = $order->total;
            $paymentClass->tendered = $order->total;
            $paymentClass->actual_date = Carbon::now()->setTimezone($concept->default_timezone)->toDateTimeString();
            $paymentClass->payment_method_hid = $payment_hid;
            $foodics->payments[] = $paymentClass;
        } else {
            $foodics->payments = [];
        }


        $products = [];

        // format products order
        $items = $order->orderItems()->get();
        foreach($items as $orderItem) {
            // A. declare a product object first
            $product = new \stdClass();

            // Add product notes
            $product->notes = $orderItem->notes? $orderItem->notes: '';

            $item = $orderItem->item;

            // These will all get overridden if item is a meal
            $product->product_hid = $item->code;
            $product->quantity = $orderItem->quantity;

            /**
             * todo: REMOVE THIS IF INCORRECT
             * Added to check for item discounts
             */
            if($orderItem->discount and $orderItem->discount != 0) {
                if($concept->id == 5) {
                    $foodics->discount_hid = '_67186697';
                }

                if($orderItem->discount <= $orderItem->item->price) {
                    $discount_percentage = ($orderItem->item->price - $orderItem->discount) / $orderItem->item->price;
                    if($discount_percentage == 0) {
                        $product->discount_amount = $orderItem->discount;
                    } else {
                        $product->discount_amount = $orderItem->item->price * $discount_percentage;
                    }
                    $foodics->discount_amount = ($foodics->discount_amount >= $product->discount_amount? $foodics->discount_amount - $product->discount_amount: 0);
                }
            }


            // HW 16/04/2018 this is being overwritten later on. I will move the discount to the bottom
            // HW 16/04/2018 actually I think this is initializing the $product->final_price, so I'm putting it back here.  Without it there was an error thrown :
            //[2018-04-16 17:15:54] lumen.ERROR: ErrorException: Undefined property: stdClass::$final_price in /var/www/solo-api/app/Api/V1/Services/FoodicsOrderService.php:264

            $product->final_price = (property_exists($product,'discount_amount') and $product->discount_amount != 0) ? ($item->price * $orderItem->quantity) - $product->discount_amount : $item->price * $orderItem->quantity;  //TODO Plus modifiers and ingredients

            // B. get all modifiers for the item and create option object
            $options = [];

            $matches = array();

            if (preg_match('/^http.*\/(\d*)$/', $orderItem->item->code, $matches) and $order->concept_id == 1) {
                // it's a meal
                $hamburginiService = new HamburginiMealService();
                $products = array_merge($products, $hamburginiService->convertMealToProducts($orderItem)->products);

                /**
                 * todo: REMOVE THIS IF INCORRECT
                 * Added to check for item discounts
                 */
                /*$products = [];
                foreach($meal_products as $product) {
                    if($orderItem->discount <= $orderItem->item->price) {
                        $discount_percentage = ($orderItem->item->price - $orderItem->discount) / $orderItem->price;
                        app('log')->debug('DISCOUNT %: '.$discount_percentage);
                        if($orderItem->discount != 0){
                            if($discount_percentage == 0) {
                                $product->discount_amount = $product->original_price;
                                $product->final_price = $product->final_price - $product->discount_amount;
                            } else {
                                $product->discount_amount = $product->original_price * $discount_percentage;
                                $product->final_price = $product->final_price - $product->discount_amount;
                            }
                        }
                    }
                    $products[] = $product;
                }*/
            }
            else {
                // not a meal
                $modifiers = $orderItem->itemOrderModifiers()->get();

                if($modifiers || count($modifiers) > 0){

                    foreach($modifiers as $orderModifier) {
                        // create option object first
                        $option = new \stdClass();

                        // check if modifier is actually a product
                        $matches = array();
                        if (preg_match('/^http.*\/(\d*)$/', $orderModifier->modifier->code, $matches)) {
                            $dummyItem = Item::findOrFail($matches[1]);
                            $dummyProduct = new \stdClass();
                            $dummyProduct->product_hid = $item->code;
                            $dummyProduct->quantity = $orderModifier->quantity;
                            $dummyProduct->final_price = $item->price * $orderModifier->quantity;  //TODO Plus modifiers and ingredients

                            foreach ($modifiers as $dummyModifier) {
                                // check if modifier is size
                                // todo verify this if correct to Hamza
                                if($dummyModifier->modifier->modifierGroup->code == 'sizes' || stripos($dummyModifier->modifier->modifierGroup->code, 'size') != false){
                                    // product size hid
                                    $dummyProduct->product_size_hid = $dummyModifier->modifier->code;
                                    $dummyProduct->original_price = $dummyModifier->modifier->price;
                                }
                            }

                        }

                        // check if modifier is size
                        // todo verify this if correct to Hamza
                        if($orderModifier->modifier->modifierGroup->code == 'sizes' || stripos($orderModifier->modifier->modifierGroup->code, 'size') != false){
                            // product size hid
                            $product->product_size_hid = $orderModifier->modifier->code;
                            $product->original_price = $orderModifier->modifier->price;
                            // added this final price if there is a modifier for sizes
                            
                            // HW 16/04/2018 - switching back to orginal calculation because it is based on the item quantity, not the size modifier quantity.
                            $product->final_price = $orderModifier->modifier->price * $orderItem->quantity;
                            //$product->final_price = $orderModifier->modifier->price * $orderModifier->quantity;
                        }

                        // check if there is a product_size_hid
                        // HW: I don't think this is in the right place - it won't be executed if there's no modifiers
                        /*

                        if(!property_exists($product,'product_size_hid')){
                            $modifier_groups = $orderItem->item->modifierGroups()->get();
                            foreach($modifier_groups as $modifier_group) {
                                if($modifier_group->code == 'sizes' || stripos($modifier_group->code,'size') != false) {
                                    $modifier = $modifier_group->modifiers()->orderBy('display_order','ASC')->take(1)->first();
                                    $product->product_size_hid = $modifier->code;
                                    $product->original_price = $modifier->price;
                                }
                            }
                        }
                        */

                        // create other modifier options if not size
                        if($orderModifier->modifier->modifierGroup->code != 'sizes' and stripos($orderModifier->modifier->modifierGroup->code, 'size') == false){
                            $option->hid = $orderModifier->modifier->code;
                            $option->quantity = $orderModifier->quantity;
                            $option->original_price = $orderModifier->modifier->price;
                            $option->final_price = $orderModifier->price * $orderModifier->quantity * $orderItem->quantity;
                            $options[] = $option;

                            // Add price to product
                            $product->final_price += $option->final_price;
                        }
                    }
                }

                // If we still don't have the size it means there were no modifiers passed and we have to retrieve the default size
                if (!property_exists($product, 'product_size_hid')) {
                    $modifierGroup = $item->modifierGroups()->where('code', 'sizes')->first();
                    if ($modifierGroup) {
                        $product->product_size_hid = $modifierGroup->modifiers()->first()->code;
                        $product->original_price = $modifierGroup->modifiers()->first()->price;
                    }
                }

                // HW:  I think in Foodics you can't ADD extra ingredients, so this block isn't necessary.
                // C. get all ingredients where the quantity > 0 because this will be an option and create option object
                $ingredients = $orderItem->itemOrderIngredients()->where('quantity','>',0)->get();
                if($ingredients || count($ingredients) > 0) {
                    foreach($ingredients as $orderIngredient) {
                        // create option object first
                        $option = new \stdClass();
                        $option->hid = $orderIngredient->ingredient->ingredient->code;
                        $option->quantity = $orderIngredient->quantity;
                        $option->original_price = $orderIngredient->ingredient->ingredient->price;
                        $option->final_price = $orderIngredient->ingredient->ingredient->price * $orderIngredient->quantity;
                        $options[] = $option;
                    }
                }

                // D. set the order array object options
                $product->options = $options;

                // E. get all ingredients where the quantity > 0 because this will be an option and create option object
                $remove_options = [];
                $removed_ingredients = $orderItem->itemOrderIngredients()->where('quantity',0)->get();
                if($removed_ingredients || count($ingredients) > 0){
                    foreach($removed_ingredients as $orderIngredient) {
                        // create remove ingredients object first
                        $option = new \stdClass();
                        $option->hid = $orderIngredient->ingredient->ingredient->code;
                        $remove_options[] = $option;
                    }
                }

                // F. set the remove ingredients array object
                $product->removed_ingredients = $remove_options;

                // HW 16/04/2018 Apply item-level discount
                if (property_exists($product,'discount_amount') and $product->discount_amount != 0) {
                    $product->final_price = $product->discount_amount >= $product->final_price? 0: $product->final_price - $product->discount_amount;
                }

                // added notes for the 01/18/2018

                // G. place the array of items/modifiers/ingredients in an product array $product object
                $products[] = $product;
            }
        }

        // set parent order object products foodics
        $foodics->products = $products;


        // return foodics order object
        return $foodics;
    }


}
