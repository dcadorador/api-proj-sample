<?php

namespace App\Console\Commands;

use App\Api\V1\Models\CustomerDevice;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncAwsSns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:sns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync and fix AWS SNS Subscription';

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
        $devices = CustomerDevice::where('updated_at','<',Carbon::now())->get();

        foreach ($devices as $device) {
            // formulate the data
            $data = [
                'customer' => $device->customer_id,
                'device-id' => $device->device_id,
                'device-token' => $device->device_token,
                'lang' => $device->lang
            ];

            $url = 'https://api.solo.skylinedynamics.com/language';

            $data_json = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Solo-Concept: 5',
                    'Content-Length: ' . strlen($data_json))
            );

            try{
                $result = curl_exec($ch);
                app('log')->debug('RESULT: '.json_encode($result));
            } catch (\Exception $e) {
                app('log')->debug('ERROR: '.$e->getMessage());
            }

            curl_close($ch);
            sleep(3);
        }
        exit;
    }
}
