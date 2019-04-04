<?php 
namespace App\Api\V1\Services;

use App\Api\V1\Models\Integration;

class SmsDelegate
{

	protected $service;
	protected $integration;

	public function __construct($concept) {

		$integration = Integration::where('concept_id', $concept)->where('type', 'sms')->first();
		$this->integration = $integration;

		if ($integration->provider == 'unifonic') {
			$this->service = new UnifonicSmsService($integration);
		}
		elseif ($integration->provider == 'jawalb') {
			$this->service = new JawalBSmsService($integration);
		}
		elseif ($integration->provider == 'yamamah') {
			$this->service = new YamamahSmsService($integration);
		}
		else {
			$this->service = new DummySmsService($integration);
		}
    }

	public function send($body, $destination) {
		$origin = $this->integration->options['origin'];
		return $this->service->send($origin, $body, $destination);
	}

}