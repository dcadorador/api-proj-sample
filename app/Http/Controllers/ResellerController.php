<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Reseller;
use App\Api\V1\Transformers\ResellerTransformer;


class ResellerController extends ApiController
{
    use \Dingo\Api\Routing\Helpers;

    public function show(Request $request)
    {
    	if (!$request->has('host')) {
    		return;
    	}

        $reseller = Reseller::where('host', 'like', '%'.$request->input('host').'%')->first();


        return $this->response->item($reseller, new ResellerTransformer, ['key' => 'reseller']);
    }


}