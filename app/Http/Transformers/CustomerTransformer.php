<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Customer;


class CustomerTransformer extends ApiTransformer
{

    public function transform(Customer $model)
    {

        $relationships = [
            'orders' => $model->uri.'/orders',
            'favorite-items' => $model->uri.'/favorites/items',
            'addresses' => $model->uri.'/addresses'  
        ];

        if($model->concepts->contains(5)) {
            $mobile = starts_with($model->mobile, '0') ? substr($model->mobile, 1) : $model->mobile ;
            $mobile = is_null($mobile) ? null : '0'.str_replace('966','',$mobile);
        } else {
            $mobile = $model->mobile;
        }

        return $this->transformAll($model, $relationships, [
            'code' => $model->code,
            'first-name' => $model->first_name,
            'last-name' => $model->last_name,
            'email' => $model->email,
            'mobile' => $mobile,
            'status' => $model->status,
            'pincode' => $model->sms_code,
            'account-type' => $model->account_type
        ]);

    }
}