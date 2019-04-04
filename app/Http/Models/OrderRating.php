<?php

namespace App\Api\V1\Models;

class OrderRating extends ApiModel
{
    protected $table = 'order_ratings';

    protected $fillable = [
        'order_id',
        'rating',
        'topic_id',
        'feedback_id'
    ];

    public function order()
    {
        return $this->belongsTo($this->ns.'\Order');
    }

    public function topic()
    {
        return $this->belongsTo($this->ns.'\Topic');
    }

    public function feedback()
    {
        return $this->belongsTo($this->ns.'\Feedback');
    }
}