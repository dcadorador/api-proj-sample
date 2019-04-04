<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Geofence;


class LocationGeofenceTransformer extends ApiTransformer
{

    public function transform(Geofence $model)
    {

        $relationships = [
        ];

        return $this->transformAll($model, $relationships, [
            'id' => $model->id,
            'inner' =>  $this->builder($model->inner),
            'outer' => $this->builder($model->outer)
        ]);

    }

    private function builder($geofence)
    {
        $coordinateArr = explode('],[',$geofence);
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