<?php

namespace App\Api\V1\Services;

use GuzzleHttp\Client;

use App\Api\V1\Models\Concept;

class UtilService
{

    public function __construct(Concept $concept) {
        $this->concept = $concept;
    }

    public function getPhoneNumberWithCountryCode($mobile) {
        $countryCode = $this->concept->dialing_code;

        return $countryCode.$this->getPhoneNumberWithoutLeadingZero($mobile);
    }

    public function getPhoneNumberWithLeadingZero($mobile) {
        return '0'.$this->getPhoneNumberWithoutLeadingZero($mobile);
    }

    public function getPhoneNumberWithoutLeadingZero($mobile) {
        $countryCode = $this->concept->dialing_code;

        // first clean
        $mobile = preg_replace('/\D/', '', $mobile);

        if (0 === strpos($mobile, $countryCode)) {
            // It starts with country code
            return substr($mobile, strlen($countryCode));
        }

        if (0 === strpos($mobile, '00'.$countryCode)) {
            // It includes the international dial prefix
            return substr($mobile, 2 + strlen($countryCode));
        }

        if (0 === strpos($mobile, '0')) {
            // Starts with zero, no country code
            return substr($mobile, 1);
        }

        // No leading zero
        return $mobile;        
    }

}