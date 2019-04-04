<?php

namespace App\Console\Commands;

use App\Api\V1\Models\Employee;
use App\Api\V1\Models\Integration;
use App\Api\V1\Models\Order;
use App\Api\V1\Services\FoodicsIntegrationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncNotExistantDrivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:drivers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and sync drivers if not existing in hamburgini';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ordersToday = Order::whereDate('created_at', date('Y-m-d'))
            //->whereIn('concept_id', [1, 5])
            ->whereIn('concept_id', [1])
            ->whereIn('type', ['deliver', 'delivery'])
            ->whereNotNull('code')
            ->get();


        foreach ($ordersToday as $order) {
            $driverCode = null;

            if ($order->driver()->count() === 0) {

                $integration = Integration::where('concept_id', $order->concept_id)->where('type', 'pos')->first();

                $integrationService = new FoodicsIntegrationService($order->concept, $integration);

                // response of order from foodics
                $foodics = $integrationService->getFoodicsOrder($order->code);

                // check if driver not existing in foodics order
                if(!isset($foodics->driver)) {
                    continue;
                }

                // get foodics driver code
                $driverCode = $foodics->driver->hid;

                app('log')->debug('Driver Code: '.$driverCode);

                // check driver on this method
                // sync driver if not found
                $integrationService->syncEmployees($driverCode);

                // check if driver exist and created from below function
                $employee = Employee::where('code', $driverCode)->first();

                // only attach if driver - order combination is not existing
                if(!$order->employees()->where('employee_order.employee_id', $employee->id)->where('employee_order.function','driver')->first()) {
                    app('log')->debug('Order has no Driver, Attaching ORDER: '.$order->id.' to Driver Employee ID: '. $employee->id);
                    $order->employees()->attach($employee->id, [
                        'function' => 'driver',
                        'created_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString(),
                        'updated_at' => Carbon::now()->setTimezone('GMT')->toDateTimeString()
                    ]);
                }
            }
        }

    }
}
