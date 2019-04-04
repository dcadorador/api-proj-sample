<?php 

namespace App\Api\V1\Services;

use App\Api\V1\Models\Integration;
use App\Api\V1\Models\Concept;

class IntegrationService 
{

	protected $integration;
	protected $concept;
	protected $client;

	public function __construct(Concept $concept, Integration $integration) {
		$this->integration = $integration;
		$this->concept = $concept;
		$this->client = new \GuzzleHttp\Client([
            'exceptions' => false,
        ]);
    }

	public function sync()
	{
		if ($this->integration->provider == 'foodics') {
			$foodicsIntegrationService = new FoodicsIntegrationService($this->concept, $this->integration);
			return $foodicsIntegrationService->sync();
		}
		elseif ($this->integration->provider == 'burgerizzr') {
			$foodicsIntegrationService = new BurgerizzrFoodicsIntegrationService($this->concept, $this->integration);
			return $foodicsIntegrationService->sync();
		}
	}


	public function syncLocations()
	{
		if ($this->integration->provider == 'foodics') {
			$foodicsIntegrationService = new FoodicsIntegrationService($this->concept, $this->integration);
			return $foodicsIntegrationService->syncLocations();
		}
	}

	public function syncEmployees()
	{
		if ($this->integration->provider == 'foodics') {
			$foodicsIntegrationService = new FoodicsIntegrationService($this->concept, $this->integration);
			return $foodicsIntegrationService->syncEmployees();
		}
	}

    public function cancelOrder($order)
    {
        if ($this->integration->provider == 'foodics') {
            $foodicsIntegrationService = new FoodicsIntegrationService($this->concept, $this->integration);
            return $foodicsIntegrationService->cancelOrder($order);
        }
    }

    // todo check if it is correct
    public function order($data)
    {
        if ($this->integration->provider == 'foodics') {
            $foodicsIntegrationService = new FoodicsIntegrationService($this->concept, $this->integration);
            return $foodicsIntegrationService->order($data);
        } elseif ($this->integration->provider == 'burgerizzr') {
            $foodicsIntegrationService = new BurgerizzrFoodicsIntegrationService($this->concept, $this->integration);
            return $foodicsIntegrationService->order($data);
        }
    }

    // added to get foodics order details
    public function getFoodicsOrder($code)
    {
        if ($this->integration->provider == 'foodics') {
            $foodicsIntegrationService = new FoodicsIntegrationService($this->concept, $this->integration);
            return $foodicsIntegrationService->getFoodicsOrder($code);
        } elseif ($this->integration->provider == 'burgerizzr') {
            $foodicsIntegrationService = new BurgerizzrFoodicsIntegrationService($this->concept, $this->integration);
            return $foodicsIntegrationService->getFoodicsOrder($code);
        }
    }


	protected function callApi($httpMethod, $uri, $headers) {
        usleep(1500000);  // sleep for 1.5 seconds to avoid Foodics rate limiter
		app('log')->debug('headers:'.json_encode($headers));
		return $this->client->request($httpMethod, $uri, [
									  'headers' => $headers
									]);
	}

    protected function postApi($httpMethod, $uri, $headers, $data) {
        app('log')->debug('data:'.json_encode($data));
        return $this->client->request($httpMethod, $uri, [
            'headers' => $headers,
            'json' => $data
        ]);
    }

    public function getHappyFoxFeedback($data)
    {
        if ($this->integration->provider == 'happyfox') {
            $happyFoxFeedbackService = new HappyFoxFeedbackService($this->concept, $this->integration);
            return $happyFoxFeedbackService->submitTicket($data);
        }
    }
}