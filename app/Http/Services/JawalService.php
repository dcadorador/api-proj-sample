<?php

namespace App\Api\V1\Services;

use GuzzleHttp\Client;

class JawalService
{

    const API_SEND = 'http://www.jawalbsms.ws/api.php/sendsms?';
    const JL_USER = 'skyline';
    const JL_PASS = 'w3qUDh';

    public function send($recipient,$message,$concept)
    {
        $client = new Client();

        // use the JAWAL API to send request
        $url = self::API_SEND.'user='.self::JL_USER.'&pass='.self::JL_PASS.'&to='.str_replace('+','',$recipient).'&message='.$message.'&sender='.$concept;
        $rs = $client->request('GET',$url)->getBody();

        // check if there is a STATUS from the results, if not then return FALSE
        if(!(strpos((string)$rs, 'STATUS') !== false))
        {
            return false;
        }

        return true;
    }

}