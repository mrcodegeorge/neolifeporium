<?php

use App\Http\Middleware\CheckIfInstalled;
use App\Http\Middleware\EnsureInstalled;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\AuditAdminActions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'not_installed' => CheckIfInstalled::class,
            'role' => EnsureUserHasRole::class,
            'audit.admin' => AuditAdminActions::class,
        ]);

        $middleware->web(prepend: [
            EnsureInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
