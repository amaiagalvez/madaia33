<?php

namespace App\Providers;

use App\Models\Owner;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Observers\OwnerAuditObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Listeners\RecordUserLoginSession;
use App\Listeners\RecordUserLogoutSession;

class DomainEventsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, RecordUserLoginSession::class);
        Event::listen(Logout::class, RecordUserLogoutSession::class);

        Owner::observe(OwnerAuditObserver::class);
    }
}
