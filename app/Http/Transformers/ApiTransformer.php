<?php

namespace App\Api\V1\Transformers;

use League\Fractal\TransformerAbstract;
use App\Api\V1\Models\ApiModel;


class ApiTransformer extends TransformerAbstract
{

	protected function transformAll($model, $relationships, $attributes)
	{
		$links = [];
		foreach ($relationships as $key => $value) {
			if ($value) {
				$links[$key] = array(
	        		'links' => [
	        			'self' => $value
	        		]
	        	);
			}
		}

		$data = [
			'id' => $model->id,
			'self' => $model->uri,
            'created-at' => $this->formatTimestamp($model->created_at),
            'updated-at' => $this->formatTimestamp($model->updated_at),
            'relationships' => $links
		];

		$data = array_merge($data, $attributes);

		if (count($model->customFields)>0) {
			$cf = [];
			foreach ($model->customFields as $customField) {
				array_push($cf, [
							'custom-field-id' => $customField->customField->id,
							'custom-field' => $customField->customField->label,
							'value' => $customField->data 
							]);
			}
			$data = array_add($data, 'custom-fields', $cf);
		}

		return $data;
	}


	protected function formatTimestamp($timestamp) {
		if ($timestamp == null) {
			return "";
		}
		if (!is_string($timestamp)) {
			return $timestamp->format('Y-m-d\TH:i:s');
		}
		$dt = new \DateTime($timestamp);
		return $dt->format('Y-m-d\TH:i:s');
	}

	protected function imageUri($model) {
		return $model->image_uri? $model->image_uri: 'https://solo.skylinedynamics.com/images/default-image.png';
	}
}