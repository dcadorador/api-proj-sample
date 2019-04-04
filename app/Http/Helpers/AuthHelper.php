<?php
namespace App\Api\V1\Helpers;

use JWTAuth;
use Illuminate\Support\Facades\Request;

class AuthHelper
{

    private static $_user;

    private function __construct()
    {
    }

    /**
     * Get the authenticated user. False if none.
     *
     * @return \App\User|false
     */
    public static function getAuthenticatedUser()
    {

        try {
            if ($token = JWTAuth::getToken()) {
                if (!static::$_user) {
                    static::$_user = JWTAuth::parseToken()->authenticate();
                }
            } else {
                static::$_user = false;
            }

            return static::$_user;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            app('log')->error('Auth Helper Error - Headers :'.json_encode(Request::header()).'. Token is invalid: '.$e->getMessage());
        }

        return false;
    }

}