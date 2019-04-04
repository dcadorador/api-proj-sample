<?php

namespace App\Api\V1\Controllers;

use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;

use Symfony;


class BackendController extends ApiController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function index (Request $request)
    {
        $user = app('Dingo\Api\Auth\Auth')->user();

        return $user;
    }
}