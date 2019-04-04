<?php
namespace App\Api\V1\Services;


class DummySmsService extends BaseIntegrationService implements SmsService {

	public function send($origin, $body, $destination) {
		app('log')->debug('[Dummy SMS] Destination: '.$destination.
						   ', Origin: '.$origin.', Body: '.$body);
		return true;
	}

}