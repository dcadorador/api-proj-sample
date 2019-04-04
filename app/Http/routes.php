<?php

use Illuminate\Database\Eloquent\Relations\Relation;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$api = app('Dingo\Api\Routing\Router');


// JWT Protected routes
/*$api->group([
    'version' => 'v1',
    'namespace' => 'App\Api\V1\Controllers',
    'middleware' => 'api.auth',
    'providers' => 'jwt'
], function($api) {
    $api->get('index', 'BackendController@index');
    /*
    $api->get('customers/{customer}','CustomerController@show');
    $api->patch('customers/{customer}','CustomerController@edit');
    $api->get('customers/{customer}/addresses','CustomerController@showAddress');
    $api->post('customers/{customer}/addresses','CustomerController@addAddress');
    $api->patch('customers/{customer}/addresses/{address}','CustomerController@editAddress');
    $api->delete('customers/{customer}/addresses/{address}','CustomerController@deleteAddress');
    $api->post('customers/{customer}/verify','CustomerController@customerSmsVerification');

    $api->get('customers/{customer}/favorites/items','FavoriteController@index');
    $api->post('customers/{customer}/favorites/items','FavoriteController@store');
    $api->delete('customers/{customer}/favorites/items/{favorite}','FavoriteController@delete');
    $api->get('customers/{customer}/orders','CustomerController@customerOrders');


    $api->get('employees','EmployeeController@index');
    $api->post('employees','EmployeeController@store');
    $api->get('employees/{employee}','EmployeeController@show');
    $api->patch('employees/{employee}','EmployeeController@edit');
    $api->get('employees/{employee}/concepts','ConceptController@getEmployeeConcepts');


});*/


// Publicly accessible routes
$api->group([
    'version' => 'v1',
    'namespace' => 'App\Api\V1\Controllers',
    'middleware' => [
        App\Http\Middleware\RequestLogMiddleware::class
    ]
], function($api)
{
    $api->post('webhook/foodics/feedback', 'OrderController@foodicsHook');
    $api->post('webhook/payfort/feedback', 'OrderController@foodicsPayfortHook');
    $api->post('webhook/hyperpay/feedback', 'HyperPayController@webhook');

    // move the language here
    $api->group([
        'middleware' => [
            App\Http\Middleware\LocaleMiddleware::class,
        ]
    ], function($api) {

        $api->post('payfort/token', 'OrderController@foodicsPayfortToken');

        // apply concept middleware
        $api->group([
            'middleware' => [
                App\Http\Middleware\ApiManagerMiddleware::class,
            ]
        ], function ($api) {

            // removed concept middleware for login
            $api->post('login', 'AuthenticateController@backend');

            $api->group([
                'middleware' => [
                    App\Http\Middleware\ConceptMiddleware::class,
                ]
            ], function ($api) {

                // adding a prefix for the cms
                $api->group([
                    'prefix' => 'cms'
                ], function ($api) {
                    $api->get('sliders', 'SliderController@index');
                    $api->get('sliders/{slider}', 'SliderController@show');
                    $api->post('sliders', 'SliderController@store');
                    $api->delete('sliders/{slider}', 'SliderController@delete');

                    $api->get('sliders/{slider}/slides', 'SlideController@index');
                    $api->post('sliders/{slider}/slides', 'SlideController@store');
                    $api->get('sliders/{slider}/slides/{slide}', 'SlideController@show');
                    $api->post('sliders/{slider}/slides/{slide}', 'SlideController@edit');
                    $api->delete('sliders/{slider}/slides/{slide}', 'SlideController@delete');
                    $api->post('notifications','CustomerDeviceController@publish');

                });


                // Mapping for polymorphic user structure
                Relation::morphMap([
                    'device' => 'App\Api\V1\Models\Device',
                    'employee' => 'App\Api\V1\Models\Employee',
                    'customer' => 'App\Api\V1\Models\Customer'
                ]);

                // Temporary public until we have admin user with JWT
                $api->get('customers', 'CustomerController@index');
                $api->get('customers/{customer}', 'CustomerController@show');
                $api->patch('customers/{customer}', 'CustomerController@edit');
                $api->get('customers/{customer}/addresses', 'CustomerController@showAddresses');
                $api->get('customers/{customer}/addresses/{address}', 'CustomerController@showAddress');
                $api->post('customers/{customer}/addresses', 'CustomerController@addAddress');
                $api->patch('customers/{customer}/addresses/{address}', 'CustomerController@editAddress');
                $api->delete('customers/{customer}/addresses/{address}', 'CustomerController@deleteAddress');
                $api->get('customers/{customer}/addresses/{address}/locations', 'CustomerController@custAddressLocLookup');
                $api->post('customers/{customer}/verify', 'CustomerController@customerSmsVerification');
                $api->get('customers/{customer}/devices','CustomerDeviceController@index');
                $api->patch('language','CustomerDeviceController@editLanguage');

                $api->get('customers/{customer}/favorites/items', 'FavoriteController@index');
                $api->delete('customers/{customer}/favorites/items', 'FavoriteController@deleteItem');
                $api->post('customers/{customer}/favorites/items', 'FavoriteController@store');
                $api->delete('customers/{customer}/favorites/items/{favorite}', 'FavoriteController@delete');
                $api->get('customers/{customer}/orders', 'CustomerController@customerOrders');

                $api->get('customers/{customer}/favorites/orders', 'CustomerController@getCustomerFavOrder');
                $api->post('customers/{customer}/favorites/orders', 'CustomerController@setCustomerFavOrder');
                $api->delete('customers/{customer}/favorites/orders/{order}', 'CustomerController@deleteCustomerFavOrder');


                $api->get('employees', 'EmployeeController@index');
                $api->post('employees', 'EmployeeController@store');
                $api->get('employees/{employee}', 'EmployeeController@show');
                $api->patch('employees/{employee}', 'EmployeeController@edit');
                $api->get('employees/{employee}/bearings', 'EmployeeController@getEmpBearings');
                $api->post('employees/{employee}/bearings', 'EmployeeController@setEmpBearings');
                $api->get('employees/{employee}/concepts', 'ConceptController@getEmployeeConcepts');
                $api->post('employees/{employee}/orders', 'EmployeeController@setOrderDriver');
                $api->get('employees/{employee}/orders', 'EmployeeController@getOrders');
                $api->get('employees/{employee}/locations', 'EmployeeController@getEmployeeLocations');

                // End of Temporary Public
                $api->get('concepts', 'ConceptController@index');
                $api->get('concepts/{concept}', 'ConceptController@show');

                $api->post('resendsms', 'CustomerController@resendSms');
                $api->post('customers', 'CustomerController@createCustomer');

                $api->get('resellers', 'ResellerController@show');

                $api->get('locations/{location}', 'LocationController@show');
                $api->patch('locations/{location}', 'LocationController@edit');
                $api->get('locations/', 'LocationController@index');
                $api->post('locations/', 'LocationController@store');

                $api->get('locations/{location}/geofences', 'LocationController@getLocationGeofence');
                $api->post('locations/{location}/geofences', 'LocationController@setLocationGeofence');

                $api->get('locations/{location}/delivery-areas', 'LocationController@getDeliveryAreas');
                $api->post('locations/{location}/delivery-areas', 'LocationController@setDeliveryAreas');

                $api->get('locations/{location}/delivery-areas/{area}', 'LocationController@getDeliveryArea');
                $api->patch('locations/{location}/delivery-areas/{area}', 'LocationController@editDeliveryArea');
                $api->delete('locations/{location}/delivery-areas/{area}', 'LocationController@deleteDeliveryArea');

                $api->get('locations/{location}/devices', 'DeviceController@index');
                $api->post('locations/{location}/devices', 'DeviceController@store');
                $api->get('locations/{location}/devices/{device}', 'DeviceController@show');
                $api->patch('locations/{location}/devices/{device}', 'DeviceController@patch');
                $api->post('locations/{location}/devices/{device}/heartbeats', 'DeviceController@heartbeat');

                $api->get('locations/{location}/inactive-items', 'LocationInactiveItemsController@index');
                $api->post('locations/{location}/inactive-items', 'LocationInactiveItemsController@store');
                $api->delete('locations/{location}/inactive-items/{item}', 'LocationInactiveItemsController@destroy');

                $api->get('locations/{location}/inactive-modifiers', 'LocationInactiveItemsController@indexModifiers');
                $api->post('locations/{location}/inactive-modifiers', 'LocationInactiveItemsController@storeModifiers');
                $api->delete('locations/{location}/inactive-modifiers/{item}', 'LocationInactiveItemsController@destroyModifiers');

                $api->get('delivery-areas', 'LocationController@getDeliveryAreasByCity');

                $api->get('/orders/statuses', 'OrderController@getAllStatuses');
                $api->get('/orders', 'OrderController@index');
                $api->post('/orders', 'OrderController@store');
                $api->post('/orders/check', 'OrderController@giveMoreDetailsAboutFailedOrder');
                $api->get('/orders/{order}', 'OrderController@show');
                $api->patch('/orders/{order}', 'OrderController@editOrder');
                $api->patch('/orders/{order}/cancellation', 'OrderController@edit');
                $api->get('/orders/{order}/items', 'OrderController@getOrderItems');
                $api->get('/orders/{order}/items/{orderItem}', 'OrderController@getOrderItemsItem');
                $api->get('/orders/{order}/statuses', 'OrderController@getOrderStatuses');
                $api->post('/orders/{order}/statuses', 'OrderController@updateOrderStatus');
                $api->get('/orders/{order}/payments', 'PaymentController@index');
                $api->post('/orders/{order}/payments', 'PaymentController@store');
                $api->get('/orders/{order}/employees', 'EmployeeController@getOrderEmployee');
                $api->get('orders/{order}/ratings/topics', 'OrderRatingsController@getTopics');

                $api->post('/orders/{order}/feedback', 'OrderFeedbackController@store');


                $api->get('locations/{location}/orders/{order}/statuses', 'OrderController@getStatusHistory');

                $api->get('menus', 'MenuController@index');
                $api->post('menus', 'MenuController@store');
                $api->get('menus/{menu}', 'MenuController@show');

                $api->get('menus/{menu}/categories', 'CategoryController@index');
                $api->post('menus/{menu}/categories', 'CategoryController@store');
                $api->get('menus/{menu}/items', 'ItemController@getAllItems');
                $api->get('menus/{menu}/items/popular', 'ItemController@getPopularItems');
                $api->get('menus/{menu}/items/recommended', 'ItemController@getRecommendedItems');
                $api->get('menus/{menu}/categories/{category}/items/recommended', 'CategoryController@getRecommendedItems');
                $api->get('menus/{menu}/categories/{category}', 'CategoryController@show');
                $api->post('menus/{menu}/categories/{category}', 'CategoryController@edit');

                $api->get('menus/{menu}/timed-events', 'TimedEventController@getMenuTimedEvents');

                $api->get('menus/{menu}/categories/{category}/items', 'ItemController@index');
                $api->post('menus/{menu}/categories/{category}/items', 'ItemController@store');
                $api->get('menus/{menu}/categories/{category}/items/{item}', 'ItemController@show');
                $api->post('menus/{menu}/categories/{category}/items/{item}', 'ItemController@edit');
                $api->get('menus/{menu}/categories/{category}/items/{item}/timed-events', 'ItemController@getTimedEvents');
                $api->get('menus/{menu}/categories/{category}/items/{item}/locations', 'ItemController@getItemLocation');

                $api->get('menus/{menu}/categories/{category}/items/{item}/modifier-groups', 'ModifierGroupController@index');
                $api->post('menus/{menu}/categories/{category}/items/{item}/modifier-groups', 'ModifierGroupController@store');
                $api->get('menus/{menu}/categories/{category}/items/{item}/modifier-groups/{modifierGroup}', 'ModifierGroupController@showItemModGrp');
                $api->patch('menus/{menu}/categories/{category}/items/{item}/modifier-groups/{modifierGroup}', 'ModifierGroupController@editItemModGrp');

                $api->get('menus/{menu}/categories/{category}/items/{item}/modifier-groups/{modifierGroup}/modifiers', 'ModifierController@index');
                $api->post('menus/{menu}/categories/{category}/items/{item}/modifier-groups/{modifierGroup}/modifiers', 'Modifier@store');
                $api->get('menus/{menu}/categories/{category}/items/{item}/modifier-groups/{modifierGroup}/modifiers/{modifier}', 'Modifier@show');

                $api->get('modifier-groups', 'ModifierGroupController@index');
                $api->post('modifier-groups', 'ModifierGroupController@store');
                $api->get('modifier-groups/{modifierGroup}', 'ModifierGroupController@show');
                $api->post('modifier-groups/{modifierGroup}', 'ModifierGroupController@edit');

                $api->get('modifier-groups/{modifierGroup}/modifiers', 'ModifierController@index');
                $api->post('modifier-groups/{modifierGroup}/modifiers', 'ModifierController@store');
                $api->get('modifier-groups/{modifierGroup}/modifiers/{modifier}', 'ModifierController@show');
                $api->post('modifier-groups/{modifierGroup}/modifiers/{modifier}', 'ModifierController@edit');

                $api->get('ingredients', 'IngredientController@index');
                $api->get('ingredients/{ingredient}', 'IngredientController@show');
                $api->post('ingredients', 'IngredientController@store');
                $api->post('ingredients/{ingredient}', 'IngredientController@edit');

                $api->get('menus/{menu}/categories/{category}/items/{item}/item-ingredients', 'ItemIngredientController@index');
                $api->get('menus/{menu}/categories/{category}/items/{item}/item-ingredients/{item-ingredient}', 'ItemIngredientController@show');
                $api->post('menus/{menu}/categories/{category}/items/{item}/item-ingredients', 'ItemIngredientController@store');
                $api->patch('menus/{menu}/categories/{category}/items/{item}/item-ingredients/{item-ingredient}', 'ItemIngredientController@edit');

                $api->get('menus/{menu}/categories/{category}/items/{item}/bundled-items', 'BundledItemController@index');
                $api->post('menus/{menu}/categories/{category}/items/{item}/bundled-items', 'BundledItemController@store');
                $api->get('menus/{menu}/categories/{category}/items/{item}/bundled-items/{bundled-item}', 'BundledItemController@show');

                $api->get('menus/{menu}/categories/{category}/items/{item}/bundled-items/{bundled-item}/bundled-categories', 'BundledCategoryController@index');
                $api->get('menus/{menu}/categories/{category}/items/{item}/bundled-items/{bundled-item}/bundled-categories/{bundled-category}', 'BundledCategoryController@show');

                $api->get('custom-fields', 'CustomFieldController@index');
                $api->post('custom-fields', 'CustomFieldController@store');
                $api->get('custom-fields/{custom-field}', 'CustomFieldController@show');
                $api->patch('custom-fields/{custom-field}', 'CustomFieldController@edit');
                $api->post('custom-fields/{custom-field}/data', 'CustomFieldController@postData');
                $api->delete('custom-fields/{custom-field}/data', 'CustomFieldController@deleteData');

                $api->get('integrations', 'IntegrationController@index');
                $api->get('integrations/{integrationType}/sync', 'IntegrationController@sync');
                $api->get('integrations/{integrationType}/sync-locations', 'IntegrationController@syncLocations');
                $api->get('integrations/{integrationType}/sync-employees', 'IntegrationController@syncEmployees');

                $api->get('roles', 'RoleController@index');

                $api->get('feedback', 'FeedbackController@index');
                $api->post('feedback', 'FeedbackController@sendFeedback');

                $api->get('feedbacks/{feedback}/ratings', 'FeedbackRatingsController@index');

                $api->post('prices', 'OrderController@getMealPrice');

                $api->get('reports/delivery-areas', 'ReportController@deliveryAreas');
                $api->get('reports/daily-sales', 'ReportController@dailySales');
                $api->get('reports/daily-summary', 'ReportController@threeDaySummary');
                $api->get('reports/overdue-orders', 'ReportController@overdueOrders');

                $api->post('forgotPassword', 'AuthenticateController@forgotPassword');

                $api->get('applications','ApplicationController@index');
                $api->post('applications','ApplicationController@store');
                $api->get('applications/{application}','ApplicationController@show');

                $api->post('exports','ExportController@exportData');

                $api->post('images','OrderController@imageUpload');

                $api->post('identify','CustomerDeviceController@identify');


                $api->post('hyperpay/checkout', 'HyperPayController@checkout');
                $api->post('hyperpay/get-payment-status', 'HyperPayController@getPaymentStatus');

                $api->get('timed-events','TimedEventController@index');
                $api->post('timed-events','TimedEventController@store');
                $api->get('timed-events/{timedEvent}','TimedEventController@show');
                $api->patch('timed-events/{timedEvent}','TimedEventController@edit');

                $api->get('timed-events/{timedEvent}/items','TimedEventController@getItems');
                $api->post('timed-events/{timedEvent}/items','TimedEventController@storeItem');

                $api->get('topics', 'TopicController@index');
                $api->post('topics', 'TopicController@store');
                $api->put('topics/{id}', 'TopicController@update');
                $api->delete('topics/{id}', 'TopicController@destroy');

                $api->get('prayer-times', 'PrayerTimesController@index');
                $api->get('cities', 'CityController@index');
                $api->post('cities', 'CityController@store');
                $api->get('cities/{city}', 'CityController@show');
                $api->get('cities/{city}/delivery-areas', 'CityController@getDeliveryAreas');
            });

        });
    });
});