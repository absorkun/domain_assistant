<?php

namespace App\Providers;

use App\Models\Domain;
use App\Support\ActivityLogger;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        $this->configureActivityLogging();
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

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureActivityLogging(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            app(ActivityLogger::class)->log(
                action: 'auth.login',
                subject: $event->user,
                description: 'User login.'
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            app(ActivityLogger::class)->log(
                action: 'auth.logout',
                subject: $event->user,
                description: 'User logout.'
            );
        });

        Event::listen(Failed::class, function (Failed $event): void {
            app(ActivityLogger::class)->log(
                action: 'auth.failed',
                description: 'Login failed.',
                properties: [
                    'email' => $event->credentials['email'] ?? null,
                ]
            );
        });

        Domain::observe(app(\App\Observers\DomainObserver::class));
    }
}
