<?php
namespace App\Api\V1\Services;


interface SmsService {

	public function send($destination, $body, $origin);

}