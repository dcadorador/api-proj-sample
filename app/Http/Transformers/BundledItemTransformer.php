<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\BundledItem;
use App\Api\V1\Models\Item;


class BundledItemTransformer extends ApiTransformer
{

    public function transform(BundledItem $model)
    {

        $relationships = [
            'parent-item' => $model->parentItem->uri,
            'item' => $model->primaryItem->uri,
            'bundled-categories' => $model->uri . '/bundled-categories'
        ];


        $bundledCategories = [];
        foreach ($model->bundledCategories as $cat) {
            $bundledCategory = [
                'default-product' => [
                    'id' => $cat->defaultItem->id,
                    'code' => $cat->defaultItem->code,
                    'name' => $cat->defaultItem->name,
                    'uri' => $cat->defaultItem->uri
                ],
                'category' => [
                    'id' => $cat->category->id,
                    'code' => $cat->category->code,
                    'name' => $cat->category->name,
                    'uri' => $cat->category->uri
                ]
            ];
            array_push($bundledCategories, $bundledCategory);
        }


        return $this->transformAll($model, $relationships, [
            'code' => $model->primaryItem->code,
            'name' => $model->primaryItem->name,
            'image-uri' => $model->primaryItem->image_uri,
            'price' => $model->primaryItem->price,
            'bundled-categories' => $bundledCategories
        ]);    
	}
}