<?php 

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldData extends ApiModel
{
    protected $table = 'custom_field_data';
    
    public function customFieldable() {
        return $this->morphTo();
    }

    public function customField() {
        return $this->belongsTo($this->ns.'\CustomField');
    }
}