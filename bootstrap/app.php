<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        
        // $exceptions->report(function (Exception $e){
        //     Log::error([
        //         'error' =>  $e->getMessage(),
        //         'file' => $e->getFile(),
        //         'Line' => $e->getLine(),
        //     ]);
        // } );
    })->create();
