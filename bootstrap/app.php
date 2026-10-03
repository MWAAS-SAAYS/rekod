<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::prefix('students')
                ->middleware(['web', 'portal:student'])
                ->group(base_path('routes/student.php'));

            Route::prefix('admin')
                ->middleware(['web', 'portal:admin'])
                ->group(base_path('routes/admin.php'));

            Route::prefix('lecturer')
                ->middleware(['web', 'portal:lecturer'])
                ->group(base_path('routes/lecturer.php'));

            Route::prefix('industrial-supervisor')
                ->middleware(['web', 'portal:industrial_supervisor'])
                ->group(base_path('routes/industrial_supervisor.php'));

            Route::prefix('company')
                ->middleware(['web', 'portal:company'])
                ->group(base_path('routes/company.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'portal' => \App\Http\Middleware\PortalMiddleWare::class,
            'ensure.attachment.selected' => \App\Http\Middleware\EnsureAttachmentSelected::class,
            'company.verified' => \App\Http\Middleware\EnsureCompanyIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();