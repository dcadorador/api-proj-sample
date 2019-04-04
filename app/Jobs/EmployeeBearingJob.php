<?php

namespace App\Jobs;

use App\Api\V1\Services\NotificationService;

class EmployeeBearingJob extends Job
{
    private $employee;
    private $bearing;
    private $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($employee,$bearing,$order)
    {
        $this->employee = $employee;
        $this->bearing = $bearing;
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
        $service->triggerDriverBearings($this->employee,$this->bearing,$this->order);
    }
}
