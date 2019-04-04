<?php 

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends ApiModel
{
    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/custom-fields/' . $this->id;
    }	
}