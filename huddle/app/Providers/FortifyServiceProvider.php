<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\RedirectsIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

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
        $this->configureViews();
        $this->configureAuthenticationPipeline();
        $this->configureRateLimiting();
        $this->applyPasswordResetRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('livewire.auth.login'));
        Fortify::verifyEmailView(fn () => view('livewire.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::resetPasswordView(fn () => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
    }

    /**
     * Lock out repeated failed logins (in addition to route throttling).
     */
    private function configureAuthenticationPipeline(): void
    {
        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                EnsureLoginIsNotThrottled::class,
                config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectsIfTwoFactorAuthenticatable::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input(Fortify::username(), '')));
            $ip = $request->ip();

            return [
                Limit::perMinute(10)->by($email.'|'.$ip),
                Limit::perMinute(30)->by($ip),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            $sessionId = (string) $request->session()->get('login.id');

            return [
                Limit::perMinute(5)->by($sessionId),
                Limit::perMinute(15)->by($request->ip()),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input('email', '')));
            $ip = $request->ip();

            return [
                Limit::perMinute(3)->by($email.'|'.$ip),
                Limit::perMinute(10)->by($ip),
            ];
        });
    }

    /**
     * Apply throttling to Fortify password reset routes.
     */
    private function applyPasswordResetRateLimiting(): void
    {
        $this->app->booted(function (): void {
            $router = $this->app->make('router');

            foreach (['password.email', 'password.update'] as $routeName) {
                $route = $router->getRoutes()->getByName($routeName);

                if ($route !== null) {
                    $route->middleware('throttle:password-reset');
                }
            }
        });
    }
}
