<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \App\Services\Contracts\AppointmentSlotServiceInterface::class,
            \App\Services\AppointmentSlotService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('partials.pagination');
        \Illuminate\Pagination\Paginator::defaultSimpleView('partials.pagination');

        // Default empty CSP nonce across views (populated per-request by SecurityHeadersMiddleware)
        \Illuminate\Support\Facades\View::share('cspNonce', '');

        // Enforce consistent application-wide password policy (SEC-BE-2026-009)
        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers();
        });

        // Route model binding: {customer} → User model (müşteri rolündeki kullanıcılar)
        Route::bind('customer', function ($value) {
            return User::withTrashed()->where('uuid', $value)->orWhere('id', $value)->firstOrFail();
        });

        // Explicit Policy Registrations
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Appointment::class, \App\Policies\AppointmentPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);

        // API Rate Limiting configuration
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Web Login Route Rate Limiting (IP-level boundary against flood abuse)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
