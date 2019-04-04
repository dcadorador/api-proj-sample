<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Integration;
use App\Api\V1\Models\IntegrationOption;

use App\Api\V1\Services\IntegrationService;

use App\Api\V1\Transformers\IntegrationTransformer;

class IntegrationController extends ApiController
{

    public function index(Request $request) {
        $concept = $this->getConcept($request);
        $integrations = Integration::where('concept_id', $concept->id);
        return $this->response->paginator($integrations->paginate($this->perPage), new IntegrationTransformer, ['key' => 'integration']);
    }

    public function sync(Request $request, $integrationType) {
        $integration = $this->getIntegration($request, $integrationType);
        $integrationService = new IntegrationService($this->getConcept($request),
                                                     $integration);
        $integrationService->sync();
    }


    public function syncLocations(Request $request, $integrationType) {
        $integration = $this->getIntegration($request, $integrationType);
        $integrationService = new IntegrationService($this->getConcept($request),
                                                     $integration);
        $integrationService->syncLocations();
    }

    public function syncEmployees(Request $request, $integrationType) {
        $integration = $this->getIntegration($request, $integrationType);
        $integrationService = new IntegrationService($this->getConcept($request),
                                                     $integration);
        $integrationService->syncEmployees();
    }

    private function getIntegration(Request $request, $integrationType) {
        return Integration::where('concept_id', $this->getConcept($request)->id)
                            ->where('type', $integrationType)->first();
    }

}
