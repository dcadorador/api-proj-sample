<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\DeliveryArea;


class DeliveryAreaTransformer extends ApiTransformer
{

    public function transform(DeliveryArea $model)
    {
        $relationships = [
            'location' => $model->location->uri
        ];


        return $this->transformAll($model, $relationships, [
            'label' => $model->label,
            'name' => $model->name,
            'coordinates' => $this->builder($model),
        ]);
    }

    private function builder($model)
    {
        $coordinateArr = explode('],[',$model->coordinates);
        $data = [];
        // loop inside array of inner/outer values.
        foreach($coordinateArr as $coordinates)
        {
            $cleanUp1 = rtrim($coordinates, "]");
            $cleanUp2 = ltrim($cleanUp1,"[");
            $array = [];
            $values = explode(',',$cleanUp2);
            // loop inside the values for the inner/outer data.
            // todo figure out how to remove the trailing column of null data
            foreach($values as $value) {
                if ($value == 0) {
                    continue;
                }
                $array[] = floatval($value);
            }
            $data[] = $array;
        }
        return $data;
    }

}