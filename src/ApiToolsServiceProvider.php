<?php namespace SmsTools;

use Illuminate\Support\ServiceProvider;

class SmsToolsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        if(config('sms.router.includeRoutes')) {
            $router->prefix(config('sms.router.prefix'))
                ->namespace('SmsTools\Http\Controllers')
                ->middleware(['api'])
                ->group(__DIR__.'/Http/api.php');
        }

        $argv = $this->app->request->server->get('argv');
        if(isset($argv[1]) and $argv[1]=='vendor:publish') {
            $this->publishes([
                __DIR__.'/../config/sms.php' => config_path('sms.php'),
            ], 'config');
            $this->publishes([
                __DIR__.'/SmsMessage.stub.php' => app_path('SmsMessage.php'),
            ], 'model');

            $existing = glob(database_path('migrations/*_create_sms_*'));
            if(empty($existing)) {
                $this->publishes([
                    __DIR__.'/../database/migrations/create_sms_messages.stub.php' => database_path('migrations/'.date('Y_m_d_His', time()).'1_create_sms_messages.php')
                ], 'migrations');
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sms.php', 'sms');

        $this->app->bind('command.smstools:setup', Commands\SetupCommand::class);

        $this->commands([
//            'command.smstools:stats',
            'command.smstools:setup',
        ]);

    }

}
