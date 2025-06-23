<?php

use App\Exceptions\ErrorLogException;
use App\Exceptions\ValidationErrorLogException;
use App\Http\Middleware\SetUserBranch;
use App\Models\ErrorLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            HandleCors::class,
            // SetUserBranch::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->report(new ErrorLogException());
        $exceptions->render(function (ValidationException $e) {
            return (new ValidationErrorLogException())($e);
        });
    })->create();
