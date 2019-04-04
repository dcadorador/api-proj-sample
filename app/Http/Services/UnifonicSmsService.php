<?php

namespace App\Api\V1\Services;
/**
 * Service for sending and managing .
 */
class UnifonicSmsService extends BaseIntegrationService implements SmsService {

    const API_SEND = 'http://api.unifonic.com/rest/Messages/Send';

    public function send($origin, $body, $destination) {

        $app_sid = $this->integration->options['appSid'];

        $rs = $this->callApiWithParams('POST', static::API_SEND, [
            'form_params'=>[
                'AppSid'=> $app_sid,
                'Recipient'=> $destination,
                'Body'=> $body
            ]
        ]);
        app('log')->info('Destination :'.$destination.'. Unifonic SMS Response: '.$rs->getBody()->getContents());
        return $rs->getStatusCode() == 201;
    }
}
