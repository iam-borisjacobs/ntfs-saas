<?php

namespace App\Providers;

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
        // Enforce strict Performance Discipline per Phase 10
        // Prevents N+1 query explosions by throwing an exception if lazy loading is attempted outside production.
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());

        // Phase 12: Override Digital Module flag dynamically from Admin Settings
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
                $digitalSetting = \Illuminate\Support\Facades\DB::table('system_settings')
                    ->where('key', 'digital_module_enabled')
                    ->value('value');
                if ($digitalSetting !== null) {
                    config(['digital_module.enabled' => $digitalSetting === 'true']);
                }
            }
        } catch (\Throwable $e) {
            // Failsafe for pre-migration states or missing tables
        }
    }
}
