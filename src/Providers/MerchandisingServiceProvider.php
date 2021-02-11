<?php

namespace Aero\Merchandising\Providers;

use Aero\Admin\AdminModule;
use Illuminate\Support\ServiceProvider;
use Aero\Common\Facades\Settings;
use Aero\Common\Providers\ModuleServiceProvider;
use Aero\Common\Settings\SettingGroup;

class MerchandisingServiceProvider extends ModuleServiceProvider
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

        Settings::group('merchandising', static function (SettingGroup $group) {
            $group->string('sortables')->default(config('merchandising.sortables'));
        });
    }

    public function register(): void
    {

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'merchandising');
        }


        AdminModule::create('merchandising')
            ->title('Merchandising')
            ->summary('Merchandise your product listings')
            ->routes(__DIR__ . '/../../routes/admin.php')
            ->route('admin.modules.merchandising.index');
    }
}
