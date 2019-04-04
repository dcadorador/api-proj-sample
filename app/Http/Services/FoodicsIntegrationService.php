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
use App\Api\V1\Models\DeliveryArea;
use App\Api\V1\Models\Modifier;
use App\Api\V1\Models\ModifierGroup;
use App\Api\V1\Models\ItemModifierGroup;
use App\Api\V1\Models\Role;
use Carbon\Carbon;

class FoodicsIntegrationService extends IntegrationService
{

	protected $headers;

	private $changeLog;

	public function __construct(Concept $concept, Integration $integration) {
		$this->integration = $integration;
		$this->concept = $concept;
		$this->changeLog = new \stdClass();
		$this->changeLog->added = array();
		$this->changeLog->updated = array();
		$this->changeLog->disabled = array();

		$apiKey = $integration->options['api_key'];
        $businessKey = $integration->options['business_key'];
		$this->headers = [
            'Authorization' => 'Bearer '.$apiKey,
            'X-business' => $businessKey
                            ];
		$this->client = new \GuzzleHttp\Client([
			'base_uri' => 'https://dash.foodics.com/api/v2/',
            'exceptions' => false,
        ]);
    }

	public function sync()
	{
        // updated the concept during sync
        $this->concept->updated_at = Carbon::now()->setTimezone('GMT')->toDateTimeString();
        $this->concept->update();

        $config = json_decode($this->integration->options['config'], true);

		app('log')->debug('Config:'.$this->integration->options['config']);	

		$menu = $this->concept->menus->first();
		if (!$menu) {
			$menu = new Menu();
			$menu->concept_id = $this->concept->id;
			$menu->label = 'Foodics Menu';

			$menu->save();
			$this->logAdded('Menu', $menu->label, '');
		}
		$categories = $menu->categories;
		$response = $this->callApi('GET', 'categories', $this->headers);

		app('log')->debug('API Status Code:'.$response->getStatusCode());
		$data = json_decode($response->getBody());
		foreach ($data->categories as $cat) {
			if (!in_array($cat->hid, $config['categories'])) {
				continue;
			}
			app('log')->debug('Found Category '.$cat->hid);
			//TODO delete old categories which are no longer in Foodics
			$is_new = false;
			$category = Category::where('code', $cat->hid)->first();
			if (!$category) {
				$category = new Category();
				$category->menu_id = $menu->id;
				$category->code = $cat->hid;

				// Set as disabled by default - client has to enable manually to "publish"
				$category->enabled = 0;

				$category->save();
				app('log')->debug('Saved Category:'.json_encode($category));
				$is_new = true;
			}
			$oldCategory = $category;

			$name = [
	            'en-us' => $cat->name->en,
	            'ar-sa' => $cat->name->ar
	        ];
	        app('log')->debug('Name:'.json_encode($name));
			$category->name = $this->canUpdateField('category.name', $is_new, $config)? $name: $category->name;
			$category->display_order = $this->canUpdateField('category.display_order', $is_new, $config)? $cat->index: $category->display_order;
			$category->image_uri = $this->canUpdateField('category.image_uri', $is_new, $config)? $cat->image_path: $category->image_uri;

			$category->update();

			app('log')->debug('OLD CATEGORY NAME: '.$oldCategory->translate('en-us')->name);
			app('log')->debug('NEW CATEGORY NAME: '.$category->translate('en-us')->name);
			app('log')->debug('OLD CATEGORY NAME: '.$oldCategory->translate('ar-sa')->name);
			app('log')->debug('NEW CATEGORY NAME: '.$category->translate('ar-sa')->name);
			app('log')->debug('OLD DISPLAY ORDER: '.$oldCategory->display_order);
			app('log')->debug('NEW DISPLAY ORDER: '.$category->display_order);
			app('log')->debug('OLD IMAGE URI: '.$oldCategory->image_uri);
			app('log')->debug('NEW IMAGE URI: '.$category->image_uri);


			if ($is_new) {
				$this->logAdded('Category', $category->code, $cat->name->en);
			}
			elseif ($category->translate('en-us')->name != $oldCategory->translate('en-us')->name || 
					$category->translate('ar-sa')->name != $oldCategory->translate('ar-sa')->name || 
					$category->display_order != $oldCategory->display_order ||
					$category->image_uri != $oldCategory->image_uri) {
				$this->logUpdated('Category', $category->code, $cat->name->en);
			}
		}
		
		$response = $this->callApi('GET', 'products', $this->headers);
		$data = json_decode($response->getBody());
		foreach ($data->products as $prod) {
			if (!in_array($prod->category->hid, $config['categories'])) {
				continue;
			}
			$category = Category::where('code', $prod->category->hid)->first();

			$is_new = false;
			$item = $category->items()->where('code', $prod->hid)->first();
			if (!$item) {
		        $item = new Item();
		        $item->category_id = $category->id;
		        $item->code = $prod->hid;

				// Set as disabled by default - client has to enable manually to "publish"
				$item->enabled = 0;

		        $item->save();
		        $is_new = true;
		    }
			$oldItem = $item;

			$name = [
	            'en-us' => $prod->name->en,
	            'ar-sa' => $prod->name->ar
	        ];
	        $item->name = $this->canUpdateField('item.name', $is_new, $config)? $name: $item->name;

			$description = [
	            'en-us' => '',
	            'ar-sa' => ''
	        ];
	        if (count(array($prod->description)) == 0) {
				$description = [
		            'en-us' => $prod->description->en,
		            'ar-sa' => $prod->description->ar
		        ];
		    }
	        $item->description = $this->canUpdateField('item.description', $is_new, $config)? $description: $item->description;

	        $item->display_order = $this->canUpdateField('item.display_order', $is_new, $config)? $prod->index: $item->display_order;
	        $item->image_uri = $this->canUpdateField('item.image_uri', $is_new, $config)?$prod->image_path: $item->image_uri;
	        $item->price = $this->canUpdateField('item.price', $is_new, $config)?$prod->sizes[0]->price: $item->price;
	        $item->update();

			if ($is_new) {
				$this->logAdded('Item', $item->code, $prod->name->en);
			}
			elseif ($item->translate('en-us')->name != $oldItem->translate('en-us')->name || 
					$item->translate('ar-sa')->name != $oldItem->translate('ar-sa')->name || 
					$item->display_order != $oldItem->display_order ||
					$item->image_uri != $oldItem->image_uri ||
					$item->price != $oldItem->price) {
				$this->logUpdated('Item', $item->code, $prod->name->en);
			}


			app('log')->debug('products/'.$item->code.' : '.$prod->name->en);

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

				$this->logAdded('Modifier Group', 'sizes', 'Sizes');
			}

			// Assign size group to item
			$itemModifierGroup = ItemModifierGroup::where('item_id', $item->id)->where('modifier_group_id', $sizesModGroup->id)->first();
			if (!$itemModifierGroup) {
				$itemModifierGroup = new ItemModifierGroup();
				$itemModifierGroup->item_id = $item->id;
				$itemModifierGroup->modifier_group_id = $sizesModGroup->id;

				// Set as disabled by default - client has to enable manually to "publish"
				$itemModifierGroup->enabled = 0;

				$itemModifierGroup->save(); 
			}

			foreach ($prod->sizes as $size) {
				$is_new = false;
				$sizeModifier = $sizesModGroup->modifiers()->where('code', $size->hid)->first();
				if (!$sizeModifier) {
					$sizeModifier = new Modifier();
					$sizeModifier->modifier_group_id = $sizesModGroup->id;
					$sizeModifier->code = $size->hid;
					$sizeModifier->save();

					// Set as disabled by default - client has to enable manually to "publish"
					$sizeModifier->enabled = 0;

					$is_new = true;
				}
				$oldSizeModifier = $sizeModifier;

				$name = [
					"en-us" => $size->name->en,
					"ar-sa" => $size->name->ar
				];
				$sizeModifier->name = $this->canUpdateField('size.name', $is_new, $config)? $name: $sizeModifier->name;
				$sizeModifier->price = $this->canUpdateField('size.price', $is_new, $config)? $size->price: $sizeModifier->price;
				$sizeModifier->display_order = $this->canUpdateField('size.display_order', $is_new, $config)? $size->index: $sizeModifier->display_order;
				$sizeModifier->update();

				if ($is_new) {
					$this->logAdded('Modifier', $sizeModifier->code, $size->name->en);
				}
				elseif ($sizeModifier->translate('en-us')->name != $oldSizeModifier->translate('en-us')->name || $sizeModifier->translate('ar-sa')->name != $oldSizeModifier->translate('ar-sa')->name || 
						$sizeModifier->display_order != $oldSizeModifier->display_order ||
						$sizeModifier->price != $oldSizeModifier->price) {
					$this->logUpdated('Modifier', $sizeModifier->code, $size->name->en);
				}

			}

			// HW 05 April 2018 - there was a change made in Sep 2017 to put all Foodics modifiers in 
			// one Modifier Group.  I don't know why. This is now being reverted because it is causing too 
			// many problems.
			/*
			// Modifiers
			$modifierGroup = $item->modifierGroups()->where('code', 'modifiers')->first();
			if (!$modifierGroup) {
				$modifierGroup = new ModifierGroup();
				$modifierGroup->concept_id = $this->concept->id;
				$modifierGroup->name = [
		            'en-us' => 'Modifiers',
		            'ar-sa' => 'معدلات'
		        ];
		        $modifierGroup->code = 'modifiers';
				$modifierGroup->save();
			}

			// Assign modifier group to item
			$itemModifierGroup = ItemModifierGroup::where('item_id', $item->id)->where('modifier_group_id', $modifierGroup->id)->first();
			if (!$itemModifierGroup) {
				$itemModifierGroup = new ItemModifierGroup();
				$itemModifierGroup->item_id = $item->id;
				$itemModifierGroup->modifier_group_id = $modifierGroup->id;
				$itemModifierGroup->save(); 
			}

			foreach ($prod->modifiers as $mod) {
				$this->syncModifiers($modifierGroup, $mod);
			}
			*/

			foreach ($prod->modifiers as $mod) {
				$modifierGroup = $this->syncModifierGroup($mod, $config);
				$itemModifierGroup = ItemModifierGroup::where('item_id', $item->id)->where('modifier_group_id', $modifierGroup->id)->first();
 				if (!$itemModifierGroup) {
app('log')->debug('MODIFIER GROUP IN NEW ITEM MODIFIER GROUP: '.$modifierGroup->id);
					$itemModifierGroup = new ItemModifierGroup();
					$itemModifierGroup->item_id = $item->id;
 					$itemModifierGroup->modifier_group_id = $modifierGroup->id;

					// Set as disabled by default - client has to enable manually to "publish"
					$itemModifierGroup->enabled = 0;

 					$itemModifierGroup->save(); 
 				}
 			}

			// Ingredients
			foreach ($prod->sizes[0]->ingredients as $ing) {
				$ingredient = Ingredient::where('code', $ing->hid)->first();
				if (!$ingredient) {
					// Create new Ingredient
					$ingredient = $this->createIngredient($ing);
				}

				$is_new = false;
				$itemIngredient = ItemIngredient::where('item_id', $item->id)->where('ingredient_id', $ingredient->id)->first();
				if (!$itemIngredient) {
					$itemIngredient = new ItemIngredient();
					$itemIngredient->item_id = $item->id;
					$itemIngredient->ingredient_id = $ingredient->id;

					// Set ingredients as disabled by default
					$itemIngredient->enabled = 0;

					$itemIngredient->save();

					$is_new = true;					
				}
				$oldItemIngredient = $itemIngredient;

				$itemIngredient->quantity = 1;
				$itemIngredient->maximum_quantity = 1;
				$itemIngredient->minimum_quantity = $ing->relationship_data->is_optional == 'true' ? 0: 1;

				$itemIngredient->update();

				if ($is_new) {
					$this->logAdded('Item->Ingredient Link', $ingredient->code, $ingredient->translate('en-us')->name);
				}
				elseif ($itemIngredient->minimum_quantity != $oldItemIngredient->minimum_quantity) {
					$this->logUpdated('Item->Ingredient Link', $ingredient->code, $ingredient->translate('en-us')->name);
				}

			}

		}

		app('log')->debug(json_encode($this->changeLog));
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
	}

	public function syncLocations() {

        // updated the concept during sync
        $this->concept->updated_at = Carbon::now()->setTimezone('GMT')->toDateTimeString();
        $this->concept->update();

		$customField = CustomField::where('concept_id', $this->concept->id)
									->where('label', 'default-delivery-zone')
									->first();

		$response = $this->callApi('GET', 'branches', $this->headers);
		$data = json_decode($response->getBody());
		foreach ($data->branches as $branch) {
			$location = Location::where('code', $branch->hid)->first();
			if (!$location) {
				$location = new Location();
				$location->code = $branch->hid;
				$location->concept_id = $this->concept->id;
				$location->country = $this->concept->country;
				$location->pos = $this->integration->type;
				$location->save();
			}
			$name = [
				'en-us' => $branch->name->en,
				'ar-sa' => $branch->name->ar
			];
			$location->name = $name;
			$location->telephone = $branch->phone;
			$location->lat = $branch->latitude;
			$location->long = $branch->longitude;
			$location->delivery_charge = $branch->service_fees? $branch->service_fees: $this->concept->default_delivery_charge;
			$o = $branch->open_from;
			$c = $branch->open_till;
			$location->opening_hours = '[{"day": 0, "open":"'.$o.':00", "closed":"'.$c.':00"}, {"day": 1, "open":"'.$o.':00", "closed":"'.$c.':00"}, {"day": 2, "open":"'.$o.':00", "closed":"'.$c.':00"}, {"day": 3, "open":"'.$o.':00", "closed":"'.$c.':00"}, {"day": 4, "open":"'.$o.':00", "closed":"'.$c.':00"}, {"day": 5, "open":"'.$o.':00", "closed":"'.$c.':00"}, {"day": 6, "open":"'.$o.':00", "closed":"'.$c.':00"}]';
			$location->promised_time_delta_delivery = $branch->delivery_promising_time? $branch->delivery_promising_time: $this->concept->default_promised_time_delta_delivery;
			$location->promised_time_delta_pickup = $branch->pickup_promising_time? $branch->pickup_promising_time: $this->concept->default_promised_time_delta_pickup;
			$location->status = $branch->accepts_online_orders == 'true'? 'active': 'inactive';
			$location->update();

			$config = json_decode($this->integration->options['config'], true);

			if (in_array('location.areas', $config['fields'])) {
				// Sync delivery areas
				if ($branch->delivery_zones) {
					app('log')->debug('Syncing delivery areas for '.$branch->hid);

					$zonesInFoodics = array();

					foreach ($branch->delivery_zones as $zone) {
						app('log')->debug('branch: '.$branch->hid.' - '.$branch->name->en);
						array_push($zonesInFoodics, $zone->hid);
						$deliveryArea = DeliveryArea::where('location_id', $location->id)
													->where('code', $zone->hid)->first();
						app('log')->debug('deliveryArea: '.$deliveryArea? $zone->hid: 'NEW');
						$area = $this->callApi('GET', 'delivery-zones/'.$zone->hid, $this->headers);
						$area_data = json_decode($area->getBody());
						if (!$deliveryArea) {
							// if new delivery area 

							app('log')->debug('Got Foodics data for delivery-zones/'.$zone->hid);

							$deliveryArea = new DeliveryArea();
							
							$name = [
								'en-us' => $area_data->delivery_zone->name->en,
								'ar-sa' => $area_data->delivery_zone->name->ar
							];
							$deliveryArea->location_id = $location->id;
							$deliveryArea->name = $name;
							$deliveryArea->label = $area_data->delivery_zone->name->en;
							$deliveryArea->code = $area_data->delivery_zone->hid;
							$deliveryArea->save();
						}

						if ($area_data->delivery_zone->coordinates) {
							$coordinates = $area_data->delivery_zone->coordinates->features[0]->geometry->coordinates;

							// Now swap longitude and latitude
							$sorted_coordinates = '';
							foreach ($coordinates[0] as $pair) {
								$sorted_coordinates .= '['.$pair[1].','.$pair[0].'],';
							}
							// remove trailing comma just in case something doesn't like it
							$sorted_coordinates = trim($sorted_coordinates, ',');
							app('log')->debug('Co-ordinates: '.$sorted_coordinates);
							$deliveryArea->coordinates = $sorted_coordinates;
							$deliveryArea->update();
						}
					}

					app('log')->debug('Number of zones in Foodics: '.count($zonesInFoodics));

					// Check for delivery areas not in Foodics, and delete them
					if ($location->areas) {
						foreach ($location->areas as $area) {
							app('log')->debug('Checking Delivery Area '.$area->id.' for deletion');
							if (!in_array($area->code, $zonesInFoodics)) {
								app('log')->debug('Deleting Delivery Area '.$area->id);

								$area->delete();
								app('log')->debug('DELETED Delivery Area '.$area->id);
							}
						}
					}
				}
			}

			$defaultDeliveryZone = $branch->delivery_zones? $branch->delivery_zones[0]->hid: 'Unknown';

			//TODO move custom field stuff to a Service or something
			$customFieldData = null;
			$customFields = $location->customFields;
			foreach ($customFields as $cf) {
				if ($cf->custom_field_id = $customField->id) {
					$customFieldData = $cf;
            		$cf->data = $defaultDeliveryZone;
            		$cf->update();
				}
			}

			if (!$customFieldData) {
				$customFieldData = new CustomFieldData();
	            $customFieldData->custom_field_id = $customField->id;
	            $customFieldData->custom_fieldable_id = $location->id;
	            $customFieldData->custom_fieldable_type = 'App\Api\V1\Models\Location';
            	$customFieldData->data = $defaultDeliveryZone;
            	$customFieldData->save();
            }

		}
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
	}


    /**
     * Sync all employees
     *
     * If employeeHid parameter is supplied with a data it returns the single employee
     *
     * with code of $employeeHid
     *
     * @param null $employeeHid
     */
	public function syncEmployees($employeeHid = null) {

		$utilService = new UtilService($this->concept);
		if(is_null($employeeHid)) {
            $response = $this->callApi('GET', 'users', $this->headers);
        } else {
            $response = $this->callApi('GET', 'users/' . $employeeHid, $this->headers);
        }

		$data = json_decode($response->getBody());

		if(isset($data->users)) {
            foreach ($data->users as $user) {
                $this->saveEmployee($utilService, $user);
            }
        }

        if(isset($data->user)) {
		    $this->saveEmployee($utilService, $data->user, $employeeHid);
        }
	}

	protected function saveEmployee($utilService, $user, $employeeHid = null)
    {
        $code = is_null($employeeHid) ? $user->hid : $employeeHid;

        $employee = $this->concept->employees()
            ->where('code', $code)
            ->first();

        if (!$employee) {
            $employee = $this->concept->employees()->create([
                'code' => $code,
                'status' => 'active',
            ]);

            $employee->assignRole(3);  // set everyone as a driver for now
        }

        $employee->email = $user->email;
        $employee->first_name = $user->name;
        $employee->employee_id = $user->employee_number;
        if ($user->mobile == '') {
            $employee->mobile = '';
            $employee->username = $user->email;
        }
        else {
            $employee->mobile = $utilService->getPhoneNumberWithCountryCode($user->mobile);
            $employee->username = $utilService->getPhoneNumberWithLeadingZero($user->mobile);
        }
        $employee->update();

        //TODO move to service
        if ($employee->username != '') {
            $apiSubscriber = ApiSubscriber::where('username', $employee->username)->where('userable_type', 'employee')->first();
            if (!$apiSubscriber) {
                try {
                    $this->createApiSubscriber(
                        'employee',
                        $employee->id,
                        $employee->username,
                        '123456'
                    );
                }
                catch (\Exception $e) {
                    app('log')->debug('Error creating API Subscriber: '.$employee->username);
                }
            }
        }
    }

	//TODO move to service
    protected function createApiSubscriber($type, $id, $username, $password) {
    	$apiSubscriber = new ApiSubscriber();
    	$apiSubscriber->userable_type = $type;
    	$apiSubscriber->userable_id = $id;
    	$apiSubscriber->username = $username;
    	$apiSubscriber->password = $password;
    	$apiSubscriber->save();
    }

	protected function createIngredient($ing) {
        // updated the concept during sync
        $this->concept->updated_at = Carbon::now()->setTimezone('GMT')->toDateTimeString();
        $this->concept->update();

		$response = $this->callApi('GET', 'inventory-items/'.$ing->hid, $this->headers);
		$data = json_decode($response->getBody());

		$ingredient = new Ingredient();
		$ingredient->code = $ing->hid;
		$ingredient->concept_id = $this->concept->id;
		$ingredient->name = [
			'en-us' => $data->inventory_item->name->en,
			'ar-sa' => $data->inventory_item->name->ar,
		];
		$ingredient->price = 0;

		$ingredient->save();

		$this->logAdded('Ingredient', $ingredient->code, $data->inventory_item->name->en);
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
		
		return $ingredient;
	}

	//DEPRECATED - see syncModifiers
	// HW 05 April 2018 - No longer deprecated; reverting back to true Foodics modifier groups
	private function syncModifierGroup($mod, $config) {
        // updated the concept during sync
        $this->concept->updated_at = Carbon::now()->setTimezone('GMT')->toDateTimeString();
        $this->concept->update();

		$response = $this->callApi('GET', 'modifiers/'.$mod->hid, $this->headers);
		app('log')->debug('modifiers/'.$mod->hid);
		app('log')->debug('Modifiers:'.$response->getBody());
		$data = json_decode($response->getBody());

		//TODO Move finders into a Service layer to enforce rules, eg. ModifierGroups must be retrieved by concept ID.
		$is_new = false;
		$modifierGroup = ModifierGroup::where('code', $mod->hid)->where('concept_id', $this->concept->id)->first();
		if (!$modifierGroup) {
			// create new group
			$modifierGroup = new ModifierGroup();
			$modifierGroup->concept_id = $this->concept->id;
			$modifierGroup->code = $mod->hid;

			$modifierGroup->save();
			$is_new = true;
		}
		$oldModifierGroup = $modifierGroup;

		$modifierGroup->name = [
			'en-us' => $data->modifier->name->en,
			'ar-sa' => $data->modifier->name->ar,
		];
		$modifierGroup->update();

		if ($is_new) {
			$this->logAdded('Modifier Group', $modifierGroup->code, $data->modifier->name->en);
		}
		elseif ($modifierGroup->translate('en-us')->name != $oldModifierGroup->translate('en-us')->name ||
				$modifierGroup->translate('ar-sa')->name != $oldModifierGroup->translate('ar-sa')->name) {
			$this->logUpdated('Modifier Group', $modifierGroup->code, $data->modifier->name->en);
		}


		foreach ($data->modifier->options as $option) {
			$is_new = false;
			$modifier = Modifier::where('modifier_group_id', $modifierGroup->id)->where('code', $option->hid)->first();
			if (!$modifier) {
				$modifier = new Modifier();
				$modifier->modifier_group_id = $modifierGroup->id;
				$modifier->code = $option->hid;

				// Set as disabled by default - client has to enable manually to "publish"
				$modifier->enabled = 0;

				$modifier->save();
			}
			$oldModifier = $modifier;

			$name = [
				'en-us' => $option->name->en,
				'ar-sa' => $option->name->ar,
			];
            app('log')->debug('GOT THIS MODIFIER: '.$option->name->en);
			$modifier->name = $this->canUpdateField('modifier.name', $is_new, $config)? $name: $modifier->name;
			$modifier->price = $this->canUpdateField('modifier.price', $is_new, $config)? $option->price: $modifier->price;
			$modifier->display_order = $this->canUpdateField('modifier.display_order', $is_new, $config)?  $option->index: $modifier->display_order;
			$modifier->update();
            app('log')->debug('ABOUT TO ADD CHANGE LOG');
			if ($is_new) {
				$this->logAdded('Modifier', $modifier->code, $option->name->en);
			}
			elseif ($modifier->translate('en-us')->name != $oldModifier->translate('en-us')->name || 
					$modifier->translate('ar-sa')->name != $oldModifier->translate('ar-sa')->name || 
					$modifier->display_order != $oldModifier->display_order ||
					$modifier->price != $oldModifier->price) {
				$this->logUpdated('Modifier', $modifier->code, $option->name->en);
			}
            app('log')->debug('CHANGE LOG ADDED');

		}
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
        app('log')->debug('EXITING FUNCTION');
		return $modifierGroup;
	}


	// DEPRECATED - HW 05 April 2018
	protected function syncModifiers($modifierGroup, $mod) {

		$config = json_decode($this->integration->options['config'], true);

		$response = $this->callApi('GET', 'modifiers/'.$mod->hid, $this->headers);
		app('log')->debug('modifiers/'.$mod->hid);
		app('log')->debug('Modifiers:'.$response->getBody());
		$data = json_decode($response->getBody());

		foreach ($data->modifier->options as $option) {
			$modifier = Modifier::where('modifier_group_id', $modifierGroup->id)->where('code', $option->hid)->first();
			$is_new = false;
			if (!$modifier) {
				$modifier = new Modifier();
				$modifier->modifier_group_id = $modifierGroup->id;
				$modifier->code = $option->hid;
				$modifier->save();
				$is_new = true;
			}
			$name = [
				'en-us' => $option->name->en,
				'ar-sa' => $option->name->ar,
			];
			$modifier->name = $this->canUpdateField('modifier.name', $is_new, $config)? $name: $modifier->name;
			$modifier->price = $this->canUpdateField('modifier.price', $is_new, $config)? $option->price: $modifier->price;
			$modifier->display_order = $this->canUpdateField('modifier.display_order', $is_new, $config)?  $option->index: $modifier->display_order;
			$modifier->update();
		}
        app('cache')->store('redis')->flush();
        app('cache')->store('file')->flush();
		return $modifierGroup;
	}


    /**
     * todo check if correct
     */
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

	private function canUpdateField($field, $is_new, $config) {
		$canUpdate = ($is_new || in_array($field, $config['fields']));
		app('log')->debug('[canUpdateField] '.$field.' = '.$canUpdate);
		return $canUpdate;
	}

	private function logAdded($type, $code, $name) {
		array_push($this->changeLog->added, $this->createChangeLogRecord($type, $code, $name));
	}

	private function logUpdated($type, $code, $name) {
		array_push($this->changeLog->updated, $this->createChangeLogRecord($type, $code, $name));
	}

	private function logDisabled($type, $code, $name) {
		array_push($this->changeLog->disabled, $this->createChangeLogRecord($type, $code, $name));
	}

	private function createChangeLogRecord($type, $code, $name) {
		$change = new \stdClass();
		$change->type = $type;
		$change->code = $code; 
		$change->name = $name;
		return $change;		
	}
}
