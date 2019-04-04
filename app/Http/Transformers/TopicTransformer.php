<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Topic;
use Illuminate\Support\Facades\Config;

class TopicTransformer extends ApiTransformer
{

    public function transform(Topic $model)
    {
        $relationships = [];

        $data = [
            'code' => $model->code,
            'name' => $model->name,
            'type' => $this->getTypes()['topics']['types'][$model->type]
        ];

        return $this->transformAll($model, $relationships, $data);
    }

    private function getTypes()
    {
        return [
            'topics' => [
                'types' => [
                    1 => 'delivery',
                    2 => 'non-delivery'
                ]
            ]
        ];
    }

}