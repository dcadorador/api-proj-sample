<?php
namespace App\Api\V1\Services;


interface CmsService {

	public function getContentByKey($key, $locale);

}