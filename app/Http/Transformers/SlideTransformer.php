<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\Slide;

class SlideTransformer extends ApiTransformer
{

    public function transform(Slide $model)
    {

        $relationships = [
            'slider' => $model->slider->uri
        ];

        $data = [
            'label' => $model->label,
            'title' => $model->title,
            'description' => $model->description,
            'starts-at' => $model->starts_at,
            'expires-at' => $model->expires_at,
            'image-uri' => $model->image_uri,
            'display-order' => $model->display_order,
            'status' => $model->status,
            'link' => $model->link,
            'link-label' => $model->link_label
        ];

        return $this->transformAll($model, $relationships, $data);
    }

}