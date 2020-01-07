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
                ->namespace('ApiTools\Http\Controllers')
                ->middleware(['api'])
                ->group(__DIR__.'/Http/api.php');
        }

        $argv = $this->app->request->server->get('argv');
        if(isset($argv[1]) and $argv[1]=='vendor:publish') {
            $this->publishes([
                __DIR__.'/../config/api.php' => config_path('api.php'),
            ], 'config');
            $this->publishes([
                __DIR__.'/SmsMessage.stub.php' => app_path('SmsMessage.php'),
            ], 'model');

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
