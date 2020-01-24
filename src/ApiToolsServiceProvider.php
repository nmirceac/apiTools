<?php namespace ApiTools;

use Illuminate\Support\ServiceProvider;

class ApiToolsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        $router->aliasMiddleware('api-tools', Http\Middleware\ApiToolsMiddleware::class);

        if(config('api.router.includeRoutes')) {
            $router->prefix(config('api.router.prefix'))
                ->namespace('App\Http\Controllers')
                ->middleware(config('api.router.middleware'))
                ->group(__DIR__.'/routes/api.php');
        }

        $argv = $this->app->request->server->get('argv');
        if(isset($argv[1]) and $argv[1]=='vendor:publish') {
            if(!file_exists(app_path('/Http/Controllers/Api'))) {
                mkdir(app_path('/Http/Controllers/Api'));
            }

            $this->publishes([
                __DIR__.'/../config/api.php' => config_path('api.php'),
                __DIR__.'/../config/larecipe.php' => config_path('larecipe.php'),
            ], ['config', 'apitools', 'adminify']);
            $this->publishes([
                __DIR__.'/../stubs/Http/Controllers/Api.php.stub' => app_path('/Http/Controllers/Api/Api.php'),
            ], ['model', 'apitools', 'adminify']);


            $this->publishes([
                base_path('vendor/binarytorch/larecipe/publishable/assets') => public_path('vendor/binarytorch/larecipe/assets'),
                __DIR__.'/../stubs/resources/views/vendor/larecipe/partials/404.blade.php.stub' => resource_path('/views/vendor/larecipe/partials/404.blade.php'),
                __DIR__.'/../stubs/resources/views/vendor/larecipe/partials/logo.blade.php.stub' => resource_path('/views/vendor/larecipe/partials/logo.blade.php'),
                __DIR__.'/../stubs/resources/views/vendor/larecipe/partials/nav.blade.php.stub' => resource_path('/views/vendor/larecipe/partials/nav.blade.php'),
                __DIR__.'/../stubs/resources/views/vendor/larecipe/partials/sidebar.blade.php.stub' => resource_path('/views/vendor/larecipe/partials/sidebar.blade.php'),
            ], ['views', 'apitools', 'adminify']);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/api.php', 'api');
        $this->mergeConfigFrom(__DIR__.'/../config/larecipe.php', 'larecipe');

        $this->app->bind('command.apitools:setup', Commands\SetupCommand::class);
        $this->app->bind('command.apitools:docs', Commands\DocsCommand::class);

        $this->commands([
            'command.apitools:setup',
            'command.apitools:docs',
        ]);

    }

}
