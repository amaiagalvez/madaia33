<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\SupportedLocales;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Notifications\ResetPassword;

class FortifyServiceProvider extends ServiceProvider
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
        $this->configureActions();
        $this->configureAuthentication();
        $this->configureViews();
        $this->configurePasswordResetUrls();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }

    /**
     * Configure authentication rules.
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request): ?User {
            $login = trim((string) $request->input(Fortify::username()));
            $password = (string) $request->input('password');
            $emailLogin = Str::lower($login);
            $dniLogin = Str::upper($login);

            if ($login === '' || $password === '') {
                return null;
            }

            $user = User::query()
                ->where(function ($query) use ($emailLogin, $dniLogin): void {
                    $query->where('email', $emailLogin)
                        ->orWhere(function ($ownerLoginQuery) use ($emailLogin, $dniLogin): void {
                            $ownerLoginQuery
                                ->whereHas('roles', function ($roleQuery): void {
                                    $roleQuery->where('name', Role::PROPERTY_OWNER);
                                })
                                ->whereHas('owner', function ($ownerQuery) use ($emailLogin, $dniLogin): void {
                                    $ownerQuery
                                        ->where('coprop1_dni', $dniLogin)
                                        ->orWhere('coprop2_dni', $dniLogin)
                                        ->orWhereRaw('LOWER(coprop1_email) = ?', [$emailLogin])
                                        ->orWhereRaw('LOWER(coprop2_email) = ?', [$emailLogin]);
                                });
                        });
                })
                ->where('is_active', true)
                ->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                return null;
            }

            $request->session()->put('locale', SupportedLocales::normalize($user->language));

            return $user;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(function () {
            return redirect()->route(SupportedLocales::routeName('private'));
        });
        Fortify::verifyEmailView(fn() => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn() => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn() => view('pages::auth.confirm-password'));
        Fortify::resetPasswordView(fn() => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn() => view('pages::auth.forgot-password'));
    }

    /**
     * Keep password reset links in the locale currently used by the visitor.
     */
    private function configurePasswordResetUrls(): void
    {
        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return route(SupportedLocales::routeName('password.reset'), [
                'token' => $token,
                'email' => $user->email,
            ]);
        });
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
