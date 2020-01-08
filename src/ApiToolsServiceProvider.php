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
            ], ['config', 'apitools', 'adminify']);
            $this->publishes([
                __DIR__.'/../stubs/Http/Controllers/Api.php.stub' => app_path('/Http/Controllers/Api/Api.php'),
            ], ['model', 'apitools', 'adminify']);

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

        $this->app->bind('command.apitools:setup', Commands\SetupCommand::class);
        $this->app->bind('command.apitools:docs', Commands\DocsCommand::class);

        $this->commands([
            'command.apitools:setup',
            'command.apitools:docs',
        ]);

    }

}
