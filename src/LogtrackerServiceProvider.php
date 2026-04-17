<?php 

namespace Obd\Logtracker;

use Illuminate\Support\ServiceProvider;

class LogtrackerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'logtracker');

        $this->publishes([
            __DIR__ . '/config/obd_tracker.php' => config_path('obd_tracker.php'),
        ], ['logtracker', 'logtracker-config']);

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/logtracker'),
        ], ['logtracker', 'logtracker-views']);

        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], ['logtracker', 'logtracker-migrations']);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/obd_tracker.php', 'logtracker');
        $this->app->register(EventServiceProvider::class);
    }
    
}
