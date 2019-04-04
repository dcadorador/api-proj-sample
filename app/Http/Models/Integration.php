<?php

namespace App\Api\V1\Models;

class Integration extends ApiModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'concept_id', 'type', 'provider'
    ];


    public function integrationOptions() {
    	return $this->hasMany($this->ns . '\IntegrationOption');
    }

    public function getOptionsAttribute() {
    	$options = array();
    	$integrationOptions = $this->integrationOptions;
    	foreach($integrationOptions as $integrationOption) {
    		$options[$integrationOption->option_key] = $integrationOption->option_value;
    	}
    	return $options;
    }

    public function getUriAttribute()
    {
        return $this->getBaseUri() . '/integrations/' .$this->id;
    }

}
