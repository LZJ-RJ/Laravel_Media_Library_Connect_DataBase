<?php

namespace Modules\MediaLibrary\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\MediaLibrary\Console\Commands\PackageSetup;

class MediaLibraryServiceProvider extends ServiceProvider
{
    protected $file;

    /**
     * @var string $moduleName
     */
    protected $moduleName = 'MediaLibrary';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'medialibrary';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        //以下五列從套件來的
        $this->file = $this->app['files'];
        $this->packagePublish();
        $this->socketRoute();
        $this->viewComp();
        $this->command();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

//    以下function從套件來的
    /**
     * publish package assets.
     *
     * @return [type] [description]
     */
    protected function packagePublish()
    {
        // resources
        $this->publishes([
            module_path('MediaLibrary', 'Resources/assets') => resource_path('assets/vendor/MediaLibrary'),
        ], 'assets');
    }

    protected function socketRoute()
    {
        Broadcast::channel('User.{id}.media', function ($user, $id) {
            return $user->id == $id;
        });
    }

    /**
     * share data with view.
     *
     * @return [type] [description]
     */
    protected function viewComp()
    {
        $data   = [];
        $config = app('config')->get('medialibrary');

        if ($config) {
            // base url
            $url = $this->app['filesystem']
                ->disk($config['storage_disk'])
                ->url('/');
            $data['base_url'] = preg_replace('/\/+$/', '/', $url);
            // upload panel bg patterns
            $pattern_path = public_path('assets/vendor/MediaLibrary/patterns');

            if ($this->file->exists($pattern_path)) {
                $patterns = collect(
                    $this->file->allFiles($pattern_path)
                )->map(function ($item) {
                    $name = str_replace('\\', '/', $item->getPathName());

                    return preg_replace('/.*\/patterns/', '/assets/vendor/MediaLibrary/patterns', $name);
                });

                $data['patterns'] = json_encode($patterns);
            }

            // share
            view()->composer('medialibrary::_library', function ($view) use ($data) {
                $view->with($data);
            });
        }
    }

    /**
     * package commands.
     *
     * @return [type] [description]
     */
    protected function command()
    {
        $this->commands([
            PackageSetup::class,
        ]);
    }
}
