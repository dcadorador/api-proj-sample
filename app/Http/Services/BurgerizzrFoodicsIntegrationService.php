<?php 

namespace App\Api\V1\Services;

use App\Api\V1\Models\ApiSubscriber;
use App\Api\V1\Models\Integration;
use App\Api\V1\Models\Concept;
use App\Api\V1\Models\Category;
use App\Api\V1\Models\CustomField;
use App\Api\V1\Models\CustomFieldData;
use App\Api\V1\Models\Employee;
use App\Api\V1\Models\Item;
use App\Api\V1\Models\Ingredient;
use App\Api\V1\Models\ItemIngredient;
use App\Api\V1\Models\Menu;
use App\Api\V1\Models\Location;
use App\Api\V1\Models\Modifier;
use App\Api\V1\Models\ModifierGroup;
use App\Api\V1\Models\ItemModifierGroup;
use App\Api\V1\Models\Role;


class BurgerizzrFoodicsIntegrationService extends FoodicsIntegrationService
{

	protected $headers;


	public function sync()
	{
		$menu = $this->concept->menus->first();
		if (!$menu) {
			$menu = new Menu();
			$menu->concept_id = $this->concept->id;
			$menu->label = 'Foodics Menu';
			$menu->save();
		}
		$categories = $menu->categories;
		$response = $this->callApi('GET', 'categories', $this->headers);

		app('log')->debug('API Status Code:'.$response->getStatusCode());
		$data = json_decode($response->getBody());
		foreach ($data->categories as $cat) {

			if (!preg_match("/ m$/", $cat->name->en)) {
				continue;
			}

			//TODO delete old categories which are no longer in Foodics
			if (!Category::where('code', $cat->hid)->exists()) {
				$name = [
		            'en-us' => str_replace(" m", "", $cat->name->en),
		            'ar-sa' => $cat->name->ar
		        ];
		        app('log')->debug('Name:'.json_encode($name));
				$category = new Category();
				$category->menu_id = $menu->id;
				$category->code = $cat->hid;
				$category->name = $name;
				$category->display_order = $cat->index;
				$category->image_uri = $cat->image_path;
				$category->save();
				app('log')->debug('Category:'.json_encode($category));
			}
		}
			
		$response = $this->callApi('GET', 'products', $this->headers);
		$data = json_decode($response->getBody());
		foreach ($data->products as $prod) {
			$category = Category::where('code', $prod->category->hid)->first();

			if (!$category) {
				continue;
			}

			$item = $category->items()->where('code', $prod->hid)->first();
			if (!$item) {
		        $item = new Item();
		        $item->category_id = $category->id;
		        $item->code = $prod->hid;
		        $item->save();
		    }

			$name = [
	            'en-us' => $prod->name->en,
	            'ar-sa' => $prod->name->ar
	        ];
	        $item->name = $name;
	        $item->display_order = $prod->index;
	        $item->image_uri = $prod->image_path;
	        $item->price = $prod->sizes[0]->price;
	        $item->update();

			app('log')->debug('products/'.$item->code);

			// Sizes
			$sizesModGroup = $item->modifierGroups()->where('code', 'sizes')->first();
			if (!$sizesModGroup) {
				$sizesModGroup = new ModifierGroup();
				$sizesModGroup->concept_id = $this->concept->id;
				$sizesModGroup->name = [
		            'en-us' => 'Sizes',
		            'ar-sa' => 'الأحجام'
		        ];
		        $sizesModGroup->code = 'sizes';
				$sizesModGroup->save();		

				$itemModifierGroup = ItemModifierGroup::where('item_id', $item->id)->where('modifier_group_id', $sizesModGroup->id)->first();
				if (!$itemModifierGroup) {
					$itemModifierGroup = new ItemModifierGroup();
					$itemModifierGroup->item_id = $item->id;
					$itemModifierGroup->modifier_group_id = $sizesModGroup->id;
					$itemModifierGroup->save(); 
				}
		
			}

			foreach ($prod->sizes as $size) {
				$sizeModifier = $sizesModGroup->modifiers()->where('code', $size->hid)->first();
				if (!$sizeModifier) {
					$sizeModifier = new Modifier();
					$sizeModifier->modifier_group_id = $sizesModGroup->id;
					$sizeModifier->code = $size->hid;
					$sizeModifier->save();
				}

				$sizeModifier->name = [
					"en-us" => $size->name->en,
					"ar-sa" => $size->name->ar
				];
				$sizeModifier->price = $size->price;
				$sizeModifier->display_order = $size->index;
				$sizeModifier->update();
			}

			// Modifiers
			foreach ($prod->modifiers as $mod) {
				$this->syncModifiers($item, $mod);
			}


			// Ingredients
			foreach ($prod->sizes[0]->ingredients as $ing) {
				$ingredient = Ingredient::where('code', $ing->hid)->first();
				if (!$ingredient) {
					// Create new Ingredient
					$ingredient = $this->createIngredient($ing->hid);
				}

				$itemIngredient = ItemIngredient::where('item_id', $item->id)->where('ingredient_id', $ingredient->id)->first();
				if (!$itemIngredient) {
					$itemIngredient = new ItemIngredient();
					$itemIngredient->item_id = $item->id;
					$itemIngredient->ingredient_id = $ingredient->id;
					$itemIngredient->save();					
				}

				$itemIngredient->quantity = 1;
				$itemIngredient->maximum_quantity = 1;
				$itemIngredient->minimum_quantity = $ing->relationship_data->is_optional == 'true' ? 0: 1;
				$itemIngredient->update();
			}

		}
	}

	protected function syncModifiers($item, $mod) {
		$response = $this->callApi('GET', 'modifiers/'.$mod->hid, $this->headers);
		app('log')->debug('modifiers/'.$mod->hid);
		app('log')->debug('Modifiers:'.$response->getBody());
		$data = json_decode($response->getBody());

		$modifierGroup = ModifierGroup::where('code', $mod->hid)->first();
		if (!$modifierGroup) {
			$modifierGroup = new ModifierGroup();
			$modifierGroup->concept_id = $this->concept->id;
			$modifierGroup->name = [
	            'en-us' => $data->modifier->name->en,
	            'ar-sa' => $data->modifier->name->en
	        ];
	        $modifierGroup->code = $mod->hid;
			$modifierGroup->save();
		}

		$itemModifierGroup = ItemModifierGroup::where('item_id', $item->id)->where('modifier_group_id', $modifierGroup->id)->first();
		if (!$itemModifierGroup) {
			$itemModifierGroup = new ItemModifierGroup();
			$itemModifierGroup->item_id = $item->id;
			$itemModifierGroup->modifier_group_id = $modifierGroup->id;
			$itemModifierGroup->save(); 
		}

		foreach ($data->modifier->options as $option) {
			$modifier = Modifier::where('modifier_group_id', $modifierGroup->id)->where('code', $option->hid)->first();
			if (!$modifier) {
				$modifier = new Modifier();
				$modifier->modifier_group_id = $modifierGroup->id;
				$modifier->code = $option->hid;
				$modifier->save();
			}
			$modifier->name = [
				'en-us' => $option->name->en,
				'ar-sa' => $option->name->ar,
			];
			$modifier->price = $option->price;
			$modifier->display_order = $option->index;
			$modifier->update();
		}

		return $modifierGroup;
	}

    public function order($data) {
        $service = new FoodicsOrderService();
        $order = $service->order($data);
        app('log')->debug('FOODICS REQUEST:'.json_encode($order));
        $response = $this->postApi('POST', 'orders', $this->headers, json_decode(json_encode($order,true)));
        //$response = $this->postApi('POST', 'https://requestb.in/1c0ghkw1', $this->headers, json_decode(json_encode($order,true)));
        app('log')->debug('FOODICS RESPONSE:'.$response->getBody());
        if ($response->getStatusCode() == 200) {
            $orderNumber = json_decode($response->getBody())->order_hid;
            $data->code = $orderNumber;
            $data->update();
        }
        //TODO what happens if order fails?
        return json_decode($response->getBody());
    }

    public function getFoodicsOrder($code) {
        $response = $this->callApi('GET', 'orders/'.$code, $this->headers);
        if ($response->getStatusCode() == 200) {
            $body = json_decode($response->getBody());
            app('log')->debug('Got reference '.$body->order->reference.' for Order '.$code);
            return $body->order;
        }
        app('log')->debug('Error calling Foodics /orders/'.$code.': '.$response->getBody());
        return null;
    }

    public function getOrderReference($code) {
        $order = $this->getFoodicsOrder($code);
        if ($order) {
            $reference = $order->reference;
            // Take last 4 digits
            $reference = (strlen($reference)>4)?substr($reference, -4):$reference;
            return $order->reference;
        }
        return null;
    }

    public function cancelOrder($code) {
        $response = $this->callApi('POST', 'orders/'.$code.'/cancel', $this->headers);
        if ($response->getStatusCode() == 200) {
            return true;
        }
        return false;
    }

}
