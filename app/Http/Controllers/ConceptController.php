<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\ApiSubscriber;
use App\Api\V1\Models\Employee;
use App\Api\V1\Models\Concept;
use App\Api\V1\Transformers\ConceptTransformer;
use Illuminate\Http\Request;
use App\Api\V1\Controllers;

class ConceptController extends ApiController
{
    public function index(Request $request)
    {
        $concepts = Concept::paginate($this->perPage);

        return $this->response->paginator($concepts, new ConceptTransformer(), ['key' => 'concept']);
    }

    public function show(Request $request, $concept)
    {
        $concept = Concept::where('id',$concept)->first();

        if(!$concept) {
            return response()->json(['error' => [
                $this->responseArray(1014,404)
            ]], 404);
        }

        return $this->response->item($concept, new ConceptTransformer, ['key' => 'concept']);

    }

    public function getEmployeeConcepts(Request $request, $employee)
    {
        $employee = Employee::where('id',$employee)->first();

        if(!$employee) {
            return response()->json(['error' => [
                $this->responseArray(1013,404)
            ]], 404);
        }

        $concepts = $employee->concepts();

        return $this->response->paginator($concepts->paginate($this->perPage), new ConceptTransformer(), ['key' => 'concept']);
    }
}