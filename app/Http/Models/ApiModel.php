<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

abstract class ApiModel extends Model
{

    protected $ns = __NAMESPACE__;

    public $baseUri;

    protected function getBaseUri()
    {
        return $this->baseUri = env('API_DOMAIN');
    }
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Indicates if the model should force an auto-incrementeing id.
     *
     * @var bool
     */
    public $incrementing = true;

    public function customFields()
    {
        return $this->morphMany($this->ns . '\CustomFieldData', 'custom_fieldable');
    }

}