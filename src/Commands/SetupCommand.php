<?php namespace SmsTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class SetupCommand extends Command
{
    use ConfirmableTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smstools:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup SmsToools';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = config('sms');
        if(empty($config)) {
            $this->error('Config not found');
        }

        $webhookRoute = route(config('sms.router.namedPrefix').'.'.config('sms.router.webhookEndpoint'));

        $this->info('Setting webhook path to store path to '.$webhookRoute);
        $setWebhook = \SmsTools\SmsMessage::setWebhook();
        if(isset($setWebhook['test'])) {
            $this->comment('Webhook path set successfully');
            if($setWebhook['test']) {
                $this->info('Webhook URL can be reached by the API');
            } else {
                $this->error('Webhook URL cannot be reached by the API');
            }
        } else if(isset($setWebhook['error'])) {
            $this->error($setWebhook['error']['message']);
        } else {
            $this->error('There was a problem setting the webhook path');
        }
    }
}