<?php namespace SmsTools\Http\Controllers;

class SmsController extends \App\Http\Controllers\Controller
{
    public function webhook()
    {
        return \App\SmsMessage::processRequest();
    }
}
