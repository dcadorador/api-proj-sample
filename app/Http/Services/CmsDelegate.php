<?php 
namespace App\Api\V1\Services;

use App\Api\V1\Models\Integration;


/**
 * Cacheing mechanism from https://gist.github.com/tojibon/3ebf2d3fe52ae57a3c5a
 */


class CmsDelegate
{

	protected $service;
	protected $integration;

	public function __construct($concept) {
		$this->enableCache = false;
    	$this->cacheLocation = storage_path() . '/.cache/'; //You must needs to create this directory under storage/ of your Laravel app

		$integration = Integration::where('concept_id', $concept)->where('type', 'cms')->first();
		$this->integration = $integration;

		if ($integration->provider == 'mcx') {
			$this->service = new McxService($integration);
		}
    }


	public function getContentByKey($key) {
		$locale = app('translator')->getLocale();

        if($this->enableCache){
            $name = md5($key.$locale);
            if ( $this->_checkCacheAvailable($name) ) {
                return $this->_readCache($name);
            }
        }

		$content = $this->service->getContentByKey($key, $locale);
        
        if($content != '' && $this->enableCache){
            $name = md5($key.$locale);
            $this->_writeCache($name, $content);
        }

        return $content;
	}




    /*
    * 
    * Checking if cache file exist
    * @ param string $name cache file name
    * 
    */
    private function _checkCacheAvailable($name){
        if($this->enableCache){
            $ttl = isset($this->integration->options['cache-ttl'])? $this->integration->options['cache-ttl']: 60;
            $cachefile = $this->cacheLocation . $name;
            $cachetime = $ttl * 60; // turn minutes into seconds
            if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile)))  {
                return true;
            } else {
                return false;
            }    
        } else {
            return false;
        }
    }
    
    /*
    * 
    * Reading cache file
    * @ param string $name cache file name
    * 
    */
    private function _readCache($name){
        $cachefile = $this->cacheLocation . $name;
        $output = file_get_contents($cachefile, FILE_USE_INCLUDE_PATH);
        return $output;
    }
    
    
    /*
    * 
    * Writing cache file with contents
    * @ param string $name cache file name
    * @ param string $content cache content to write on cache file
    * 
    */
    private function _writeCache($name, $content){
        if($this->enableCache){
            $cachefile = $this->cacheLocation . $name;
            $fp = fopen($cachefile, 'w'); 
            fwrite($fp, $content); 
            fclose($fp);     
        }        
    }
}