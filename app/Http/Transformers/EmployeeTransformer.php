<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Employee;


class EmployeeTransformer extends ApiTransformer
{

    public function transform(Employee $model)
    {

        $relationships = [
            'concepts' => $model->uri.'/concepts',
            'bearings' => $model->uri.'/bearings',
            'locations' => $model->uri.'/locations'
        ];

        return $this->transformAll($model, $relationships, [
                'employee-id' => $model->employee_id,
                'username' => $model->user()->value('username'),
                'first-name' => $model->first_name,
                'last-name' => $model->last_name,
                'email' => $model->email,
                'mobile' => $model->mobile,
                'status' => $model->status,
                'roles' => $model->roles()->pluck('label')
        ]);

    }

}