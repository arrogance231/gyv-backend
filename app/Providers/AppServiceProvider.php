<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(\App\Models\Campaign::class, \App\Policies\CampaignPolicy::class);

        // Dynamic Permission Gate Mapping
        try {
            if (Schema::hasTable('permissions')) {
                Permission::all()->each(function ($permission) {
                    Gate::define($permission->slug, function ($user) use ($permission) {
                        return $user->hasPermission($permission->slug);
                    });
                });
            }
        } catch (\Throwable $e) {
            // Silence error during initial console commands / migrations
        }
    }
}
