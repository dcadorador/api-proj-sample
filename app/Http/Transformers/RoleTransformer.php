<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Role;

class RoleTransformer extends ApiTransformer
{

    public function transform(Role $model)
    {

        $relationships = [];

        $data = [
            'name' => $model->name,
            'label' => $model->label,
        ];

        return $this->transformAll($model, $relationships, $data);
	}

}