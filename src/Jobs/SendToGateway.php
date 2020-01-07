<?php namespace SmsTools\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendToGateway implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($messageId)
    {
        $this->message = \App\SmsMessage::find($messageId);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $success = $this->message->sendToGateway();
        if(!$success) {
            throw new \Exception('Sending to gateway failed');
        }
    }
}
