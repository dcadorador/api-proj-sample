<?php

namespace App\Api\V1\Models;

class Slide extends ApiModel
{
    use \igaster\TranslateEloquent\TranslationTrait;

    protected $table = 'slides';

    protected static $translatable = ['title','description','link_label'];

    protected $fillable = [
        'slider_id',
        'label',
        'title',
        'description',
        'starts_at',
        'expires_at',
        'display_order',
        'status',
        'link'
    ];

    public function slider()
    {
        return $this->belongsTo($this->ns.'\Slider');
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/slides/' .$this->id;
    }

}