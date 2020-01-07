# apiTools
API Tools

## Contents
1. Intro
2. Examples

# 1. Intro

## How to install?

- composer require nmirceac/api-tools
- php artisan vendor:publish
- php artisan migrate
- check config/api.php (just in case)
- add your API details to .env
- php artisan apitools:setup
- check the examples below
- enjoy! 

## Samples

### .env sample config


## Examples

### Sending a text message

\App\SmsMessage::queue('27794770189', 'Hello text world!');

### Checking your actual SMS content (allowed 8bit chars only)

dd(\App\SmsMessage::cleanContent('Enter your desired content');

