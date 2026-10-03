<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent key length errors on legacy MySQL/MariaDB utf8mb4 schema migrations
        Schema::defaultStringLength(191);

        // Enforce strict Eloquent behavior (prevent N+1 queries, unguard violations) in local environments
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}