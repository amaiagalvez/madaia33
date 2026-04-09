<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
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
        $this->configureDefaults();
        $this->registerLegacyBladeComponentAliases();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }

    /**
     * Keep backward-compatible component names while templates are organized in folders.
     */
    protected function registerLegacyBladeComponentAliases(): void
    {
        Blade::component('auth.auth-header', 'auth-header');
        Blade::component('auth.auth-session-status', 'auth-session-status');
        Blade::component('front.notice-card', 'notice-card');
        Blade::component('front.public-brand-link', 'public-brand-link');
        Blade::component('front.public-page-header', 'public-page-header');
    }
}
