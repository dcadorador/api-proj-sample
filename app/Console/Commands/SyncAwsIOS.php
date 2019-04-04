<?php

namespace App\Console\Commands;

use App\Api\V1\Models\CustomerDevice;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Api\V1\Services\SnsService;

class SyncAwsIOS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:ios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync and fix AWS SNS IOS Subscription';

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
        $devices = CustomerDevice::orWhere('model','LIKE','%ip%')
            ->whereNotIn('id',[814,839])
            ->get();

        $arn = 'arn:aws:sns:eu-west-1:390578801229:app/APNS/BurgerizzrProdIos';
        foreach ($devices as $device) {
            $sns = new SnsService();

            if($device->endpoint_arn or $device->endpoint_arn != '' or is_null($device->endpoint_arn)) {
                $endpointAtt = $sns->getEndpointAttributes($device->endpoint_arn,$device->device_token);
                app('log')->debug('END POINT ATTR: '.json_encode($endpointAtt));
                $amazonCustomData = $device->device_token." ".$device->device_id;

                try{
                    $endpointArn = $sns->registerDevice($amazonCustomData, $arn, $device->device_token);
                    app('log')->debug('END POINT ARN: '.json_encode($endpointArn));
                    $device->endpoint_arn = $endpointArn;
                    $device->update();
                } catch (\Exception $e) {
                    app('log')->debug('Error: '.json_encode($e->getMessage()));
                }
            }

            sleep(2);
        }
        exit;
    }
}
