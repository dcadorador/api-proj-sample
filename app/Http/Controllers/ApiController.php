<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use Laravel\Lumen\Routing\Controller as BaseController;

use App\Api\V1\Models\ApiSubscriber;
use App\Api\V1\Models\Concept;
use App\Api\V1\Models\ApiResponse;
use Carbon\Carbon;

class ApiController extends BaseController
{
    use \Dingo\Api\Routing\Helpers;

	protected $perPage = 50;

    protected function getConcept(Request $request, $update = false) {
        //TODO actual implementation
        //return Concept::where('id', '1b29fc01-3a15-4b93-9f73-842f81d7c7ec')->first();
        $concept = $request->attributes->get('concept');

        if($update){
            $concept->updated_at = Carbon::now()->setTimezone('GMT')->toDateTimeString();
            $concept->update();
        }

        return $concept;
    }

	/**
	 * Convert i18n input into object able to be set as an instance variable.
	 *
	 * Point to note: If a value for the application's default locale is included this will be used
	 * as default for all missing locales.
	 * 
	 */
	protected function getLocalizedInput(Request $request, $parameterName) {
		$array = array();
        if ($request->has($parameterName)) {
            foreach ($request->input($parameterName) as $locale => $value) {
                $array[$locale] = $value;
            }
        }
        return $array;
	}

	/**
	 * Store uploaded file into S3 and return file URI (or null if request does not contain
	 * required file)
	 */
    protected function saveUploadedFile(Request $request, $parameterName = 'image') {
        if ($request->hasFile($parameterName)) {
            $file = $request->file($parameterName);
            $filename = time().uniqid().'_'.$file->getClientOriginalName();
            \Storage::disk('s3')->put($filename, fopen($file, 'r+'), 'public');
            return \Storage::disk('s3')->url($filename);
        }
        return null;
    }

    protected function saveUploadedExport($file) {
            $filename = str_replace(storage_path().'/exports/','',$file);
            \Storage::disk('exports')->put($filename, fopen($file, 'r+'), 'public');
            return \Storage::disk('exports')->url($filename);
    }


    protected function createApiSubscriber($type, $id, $username, $password, $client_id) {
    	$apiSubscriber = new ApiSubscriber();
    	$apiSubscriber->userable_type = $type;
    	$apiSubscriber->userable_id = $id;
    	$apiSubscriber->username = $username;
    	$apiSubscriber->password = $password;
        $apiSubscriber->client_id = $client_id;
    	$apiSubscriber->save();
    }

    protected function generateCode()
    {
        //return 123456;
        return intval( "0" . rand(1,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9));
    }

    protected function split_name($name)
    {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
        return array($first_name, $last_name);
    }

    protected function responseArray($code,$status)
    {
        $response = ApiResponse::findByCode($code);
        return [
            'code' => $response->code,
            'detail' => $response->message,
            'status' => $status
        ];

    }

    /**
     * @param $request
     * @param $item
     * @return mixed
     */
    protected function filterByEnabled($request, $item) {
        $filter = $request->input('filter', []);
        if(array_key_exists('enabled', $filter)){
            $enabled = $filter['enabled'];
            if ($enabled == 'all') {
                return $item;
            }
            else {
                return $item->where('enabled', ($enabled=='1' || $enabled=='true')? true: false);
            }
        }
        else {
            return $item->where('enabled', true);
        }
    }

    /**
     * Limit data using page[size] in query string
     * 
     * @param $request
     * @param $item
     * @return mixed
     */
    protected function pageSize($request, $item) {
        $page = $request->input('page', []);

        if(is_array($page)) {
            if(array_key_exists('size', $page)) {
                $item = $item->paginate($page['size'] > 200 ? 200 : $page['size']);
            } else {
                $item = $item->paginate($this->perPage);
            }
        } else {
            $item->paginate($this->perPage);
        }

        return $item;
    }

    /**
     * @param $request
     */
    protected function getLocale($request)
    {
        return $request->server('HTTP_ACCEPT_LANGUAGE',env('APP_FALLBACK_LOCALE'));
    }
}