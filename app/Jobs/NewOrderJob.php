<?php

namespace App\Jobs;

use App\Api\V1\Services\NotificationService;

class NewOrderJob extends Job
{
    private $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $service = new NotificationService();
        $service->triggerNewOrder($this->order);
    }
}
