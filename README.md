# smsTools
Sms Tools

## Contents
1. Intro
2. Examples

# 1. Intro

## How to install?

- composer require nmirceac/sms-tools
- php artisan vendor:publish
- php artisan migrate
- check config/sms.php (just in case)
- add your API details to .env
- php artisan smstools:setup
- check the examples below
- enjoy! 

## Samples

### .env sample config

SMS_API_ENDPOINT="https://sms.weanswer.it/api/v1/sms"
SMS_API_KEY="AAAAAAAAA"
SMS_API_SECRET="ZZZZZZZZZZZZZZZZZZZ"


## Examples

### Sending a text message

\App\SmsMessage::queue('27794770189', 'Hello text world!');

### Checking your actual SMS content (allowed 8bit chars only)

dd(\App\SmsMessage::cleanContent('Enter your desired content');

