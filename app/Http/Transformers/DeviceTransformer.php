<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\ApiSubscriber;
use App\Api\V1\Models\Device;


class DeviceTransformer extends ApiTransformer
{

    public function transform(Device $model)
    {
    	$relationships = [];

        return $this->transformAll($model, $relationships, [
		    'label' => $model->label,
		    'last-seen-at' => $model->last_heartbeat_at,
		    'device-uuid' => $model->device_uuid
		]);    
	}
}