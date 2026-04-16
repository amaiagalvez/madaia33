<?php

namespace App\Providers;

use Throwable;
use App\Models\Owner;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\View;
use App\Observers\OwnerAuditObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use App\Support\ConfiguredMailSettings;
use Illuminate\Support\ServiceProvider;
use App\Listeners\RecordUserLoginSession;
use Illuminate\Validation\Rules\Password;
use App\Listeners\RecordUserLogoutSession;
use App\Http\Composers\BrandingSettingsComposer;
use App\Http\Composers\VotingsNavigationComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerMessagingRateLimiters();
        $this->applyConfiguredMailSettings();
        $this->registerLegacyBladeComponentAliases();
        $this->registerViewComposers();
        $this->registerAuthSessionListeners();

        Owner::observe(OwnerAuditObserver::class);
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

    protected function registerMessagingRateLimiters(): void
    {
        RateLimiter::for('campaign-email-send', fn(): Limit => Limit::perMinute(10)->by('campaign-email-send'));
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

    protected function applyConfiguredMailSettings(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (Throwable $exception) {
            report($exception);

            return;
        }

        app(ConfiguredMailSettings::class)->apply(Setting::stringValues([
            'from_address',
            'from_name',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
        ]));
    }

    /**
     * Register view composers for front-end navigation and other shared components.
     */
    protected function registerViewComposers(): void
    {
        View::composer([
            'layouts.front.main',
            'layouts::front.main',
        ], VotingsNavigationComposer::class);

        View::composer([
            'components.front.public-brand-link',
            'front.public-brand-link',
            'components.layouts.admin.main',
            'components.layouts.front.main',
            'layouts.front.main',
            'layouts::front.main',
            'layouts.admin.main',
            'layouts::admin.main',
            'layouts.shared.auth.simple',
            'layouts::shared.auth.simple',
            '*::shared.auth.simple',
            'layouts.shared.auth.split',
            'layouts::shared.auth.split',
            '*::shared.auth.split',
            'layouts.shared.auth.card',
            'layouts::shared.auth.card',
            '*::shared.auth.card',
            'partials.shared.head',
        ], BrandingSettingsComposer::class);
    }

    protected function registerAuthSessionListeners(): void
    {
        Event::listen(Login::class, RecordUserLoginSession::class);
        Event::listen(Logout::class, RecordUserLogoutSession::class);
    }
}
