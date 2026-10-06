<?php

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureBackOfficeAccess;
use App\Http\Middleware\EnsureManagementIsReadOnly;
use App\Http\Middleware\EnsureRegistrationEmailIsVerified;
use App\Http\Middleware\EnsureSuperAdminAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [EnsureRegistrationEmailIsVerified::class, EnsureManagementIsReadOnly::class]);

        $middleware->alias([
            'backOfficeAccess' => EnsureBackOfficeAccess::class,
            'superAdminAccess' => EnsureSuperAdminAccess::class,
            'adminAccess' => EnsureAdminAccess::class,
        ]);
        $middleware->redirectTo(
            guests: '/account/login',
            users: '/account/profile'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
