<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Models\Application;
use App\Api\V1\Transformers\ApplicationTransformer;

class ApplicationController extends ApiController
{
    public function index(Request $request)
    {
        $concept = $this->getConcept($request);

        $applications = $concept->applications->paginate($this->perPage);

        return $this->response->paginator($applications, new ApplicationTransformer,['key' => 'application']);
    }

    public function store(Request $request)
    {
        $concept = $this->getConcept($request);

        $application = new Application();
        $application->concept_id = $concept->id;
        $application->google_arn = $request->input('google-arn');
        $application->apple_arn = $request->input('apple-arn');
        $application->web_arn = $request->input('web-arn');
        $application->broadcast_topic_arn = $request->input('broadcast-topic-arn');
        $application->save();

        return $this->response->item($application, new ApplicationTransformer,['key' => 'application']);
    }

    public function show(Request $request, $application)
    {
        $application = Application::find($application);

        if(!$application){
            return response()->json(['error' => [
                $this->responseArray(1002,404)
            ]], 404);
        }

        return $this->response->item($application, new ApplicationTransformer,['key' => 'application']);
    }

}