<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Reseller;


class ResellerTransformer extends ApiTransformer
{

    public function transform(Reseller $model)
    {

        $relationships = [
            'clients' => $model->uri.'/clients'
        ];

        return $this->transformAll($model, $relationships, [
            'label' => $model->label,
            'logo-uri' => $model->logo_uri,
            'theme' => $model->theme,
        ]);

	}
}