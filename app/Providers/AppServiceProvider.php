<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Debug\ExceptionHandler;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot(ExceptionHandler $handler)
	{
		//TODO make this work
        $handler->register(function(ModelNotFoundException $e){
            throw new NotFoundHttpException($e->getMessage(), null, $e);
        });

	}
}
