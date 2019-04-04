<?php 

namespace App\Api\V1\Services;



class McxService extends BaseIntegrationService implements CmsService
{
  
    
    /*
    getContentByKey method
    
    @return string content value
    */
    public function getContentByKey($key, $locale) {

        if ($locale == 'en-us') {
            $locale = 'en_US';
        }
        elseif ($locale == 'ar-sa') {
            $locale = 'ar_SA';
        }

        $url = $this->integration->options['content-url'];
        $response = $this->callApi('GET', $url, []);
        if ($response->getStatusCode() != 200) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException;
        }
        $content = json_decode($response->getBody(), true);
        return $content['data'][0]['fields'][$key][$locale];
    }
}