<?php

namespace App\Api\V1\Services;


class YamamahSmsService extends BaseIntegrationService implements SmsService {

    const API_SEND = 'http://api.yamamah.com/SendSMS';


    public function send($origin, $body, $destination){
        // get the username and password
        $username = $this->integration->options['username'];
        $password = $this->integration->options['password'];
        // form the data
        $data = [
            'Username' => trim($username),
            'Password' => trim($password),
            'Tagname' => trim($origin),
            'RecepientNumber' => str_replace('+','',$destination),
            //'VariableList' => '',
            //'ReplacementList' => '',
            'Message' => $body,
            'SendDateTime' => 0,
            'EnableDR' => false
        ];

        $rs = $this->client->request('POST',static::API_SEND,[
            'json' =>  json_decode(json_encode($data),true)
        ]);

        // added logging for sms error in yamamah
        app('log')->debug('Destination: '.$destination.'. Yamamah SMS Response: '.json_encode($rs->getBody()->getContents()));

        $rs = json_decode($rs->getBody());
        return $rs->Status == 1;
    }
}