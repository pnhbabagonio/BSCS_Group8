<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Event::class => \App\Policies\EventPolicy::class,
        // Remove RegistrationPolicy reference if it doesn't exist
        // Registration::class => RegistrationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Event gates
        Gate::define('update-event', function (User $user, Event $event) {
            return $user->id === $event->user_id;
        });

        Gate::define('delete-event', function (User $user, Event $event) {
            return $user->id === $event->user_id;
        });

        Gate::define('manage-event', function (User $user, Event $event) {
            return $user->id === $event->user_id || ($user->is_admin ?? false);
        });

        // Registration gates
        Gate::define('view-registrants', function (User $user, Event $event) {
            return $user->id === $event->user_id || ($user->is_admin ?? false);
        });

        Gate::define('manage-registration', function (User $user, Registration $registration) {
            return $user->id === $registration->user_id ||
                $user->id === $registration->event->user_id ||
                ($user->is_admin ?? false);
        });

        // Additional gates for specific actions
        Gate::define('promote-waitlist', function (User $user, Event $event) {
            return $user->id === $event->user_id || ($user->is_admin ?? false);
        });

        Gate::define('update-attendance', function (User $user, Event $event) {
            return $user->id === $event->user_id || ($user->is_admin ?? false);
        });
    }
}
