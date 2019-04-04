<?php

namespace App\Providers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;

use App\Listeners\EventListener;
use App\Listeners\GuzzleHttpClient;

final class HttpAnalyzerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var Repository $config */
        $config = app('config');
        
        $this->publishes([
            __DIR__ . '/../../config/http_analyzer.php' => config_path('http_analyzer.php'),
        ]);
        
        //
        // Hook on events
        //
        
        $event = app(Dispatcher::class);
        // replaced by ApiManager middleware
        //$event->listen('kernel.handled', EventListener::class . '@onRequestHandled');  
        $event->listen(QueryExecuted::class, EventListener::class . '@onDatabaseQueryExecuted');
        $event->listen('illuminate.log', EventListener::class . '@onLog');
        
    }
    
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/http_analyzer.php', 'http_analyzer'
        );
        
        
        // Make sure event listener has just single instance
        $this->app->singleton(EventListener::class, function ($app) {
            return new EventListener(
                $app[Repository::class]
            );
        });
        
        //
        // Prepare Guzzle Http Client to communicate with API backend
        //
        $this->app->bind(GuzzleHttpClient::class, function ($app) {
            $config = app('config');
            
            $api_host = $config->get('http_analyzer.api_host', 'backend.apideveloper.io');
            $api_key  = $config->get('http_analyzer.api_key');
            
            return new GuzzleHttpClient([
                'base_uri' => 'https://' . $api_host,
                'http_errors' => false,
                'query' => ['api_key' => $api_key],
            ]);
        });
        
    }
}