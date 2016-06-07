<?php
namespace Kaleidoscope\Providers;

class LaravelServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configures
        $this->publishConfigure();

        // Publish migrations
        $this->publishMigration();
    }

    protected function publishConfigure()
    {
        $this->publishes([
            __DIR__.'/../../config/kaleidoscope.php' => config_path('kaleidoscope.php')
        ], 'config');
    }

    protected function publishMigration()
    {
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }
}
