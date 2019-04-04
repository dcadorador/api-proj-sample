<?php

namespace App\Api\V1\Services;

use GuzzleHttp\Client;

class JawalBSmsService  extends BaseIntegrationService implements SmsService {


    const API_SEND = 'http://www.jawalbsms.ws/api.php/sendsms?';

    public function send($origin, $body, $destination) {
        $username = $this->integration->options['username'];
        $password = $this->integration->options['password'];

        $url = self::API_SEND.'user='.$username.'&pass='.$password.'&to='.str_replace('+','',$destination).'&message='.$body.'&sender='.$origin;
        $rs = $this->callApi('GET', $url, []);
        // added logging for sms error in yamamah
        app('log')->debug('JAWAL SMS Response: '.json_encode($rs->getBody()->getContents()));

        // check if there is a STATUS from the results, if not then return FALSE
        if($rs->getStatusCode() == 200)
        {
            return true;
        }

        return false;
    }

}