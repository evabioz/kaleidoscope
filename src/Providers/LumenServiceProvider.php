<?php
namespace Kaleidoscope\Providers;

class LumenServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $config_paht = base_path('config/kaleidoscope.php');

        if (! file_exists($config_paht)) {
            $config_paht = __DIR__ . '/../../config/kaleidoscope.php';
        }

        $this->mergeConfigFrom(realpath($config_paht), 'kaleidoscope');
    }
}
