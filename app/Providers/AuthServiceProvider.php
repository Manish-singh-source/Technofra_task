<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Project;
use App\Policies\LeadPolicy;
use App\Policies\ProjectPolicy;
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
        Lead::class => LeadPolicy::class,
        Project::class => ProjectPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2'])) {
                return true;
            }

            $rawRole = strtolower((string) ($user?->getRawOriginal('role') ?? $user?->role ?? ''));

            if (in_array($rawRole, ['admin', 'super-admin', 'super_admin', 'super_admin2'], true)) {
                return true;
            }

            return null;
        });
    }
}
