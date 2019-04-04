<?php

namespace App\Api\V1\Transformers;

use App\Api\V1\Models\TimedEvent;

class TimedEventTransformer extends ApiTransformer
{

    protected $defaultIncludes = [
        'items'
    ];

    public function transform(TimedEvent $model)
    {

        $relationships = [
            'items' => $model->uri .'/items'
        ];

        $data = [
            'label' => $model->label,
            'is-active' => $model->is_active,
            'value' => $model->value,
            'from-date' => $model->from_date,
            'to-date' => $model->to_date,
            'event-times' => json_decode($model->event_times)
        ];

        return $this->transformAll($model, $relationships, $data);
    }

    public function includeItems(TimedEvent $model)
    {
        $items = $model->items->where('enabled',1);

        return $this->collection($items, new ItemTransformer(),'item');
    }

}