<?php

namespace Aero\Merchandising\Providers;

use Aero\Admin\AdminModule;
use Illuminate\Support\ServiceProvider;

class MerchandisingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('aero/merchandising.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../../resources/js' => public_path('vendor/merchandising/js'),
            ], 'merchandising');

        }

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'merchandising');
    }

    public function register(): void
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'aero.merchandising');
        }


        AdminModule::create('merchandising')
            ->title('Merchandising')
            ->summary('Merchandise your product listings')
            ->routes(__DIR__ . '/../../routes/admin.php')
            ->route('admin.modules.merchandising.index');
    }
}
