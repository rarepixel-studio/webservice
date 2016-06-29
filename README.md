# Webservice Client for opilo.com panel

## Usage

First, install the `opilo/webservice` package through [Composer PHP dependency manager](https://getcomposer.org/):

    composer require opilo/webservice

**Note**: if you like to know more about composer, please visit [https://getcomposer.org/](https://getcomposer.org/).

After installing the composer package, in order to send and receive SMS via opilo.com panel, you should create an instance object of class OpiloClient\V2\HttpClient.
For that, first you need to configure your webservice in [the configuration page](http://bpanel.opilo.com/api).

## Create a Client Object
```php
use OpiloClient\V2\HttpClient;
...
$client = new HttpClient('YOUR_WEBSERVICE_USERNAME', 'YOUR_WEBSERVICE_PASSWORD');
```
## Sending SMS
### Sending a Single SMS
```php
use OpiloClient\Request\OutgoingSMS;
...
$message = new OutgoingSMS('3000****', '0912*******', 'Hello World!');
$responses = $client->sendSMS($message);
```
### Sending a Batch of SMS at Once

```php
$messages = [
    new OutgoingSMS('3000****', '0912*******', 'Hello World!'),
    new OutgoingSMS('3000****', '0912*******', 'Hello World!'),
];
$response = $client->sendSMS($messages);
```
### User defined ids
In case of network errors, you may resend your SMS to be sure it is delivered to the Opilo server, but you don't want it to be sent to the target more than once.
To prevent duplicate SMSes you can set unique strings as uid fields of the `OutgoingSMS` objects.
In case of receiving a SMS with a duplicate uid, the Opilo server drops that SMS and return an SMSId object with a boolean `duplicate` flag.
The duplication of a `uid` is checked only against the messages sent during the last 24 hours.

```php
$messages = [
    new OutgoingSMS('3000****', '0912*******', 'Dont send this twice!', $some_unique_identifier_for_this_sms),
];
```
### Parsing The Return Value of sendSMS()
```php
use OpiloClient\Response\SMSId;
use OpiloClient\Response\SendError;
...
for ($i = 0; $i < count($response); $i++) {
    if ($response[$i] instanceof SMSId) {
        //store $response[$i]->id as the id of $messages[$i] in your database and schedule for checking status if needed
    } elseif ($response[$i] instanceof SendError) {
        //It could be that you run out of credit, the line number is invalid, or the receiver number is invalid.
        //To find out more examine $response[$i]->error and compare it against constants in SendError class
    }
}
```

## Check the Inbox by Pagination
```php
$minId = 0;
while (true) {
    $inbox = $client->checkInbox($minId);
    $messages = $inbox->getMessages();
    if (count($messages)) {
        foreach ($messages as $message) {
            //Process $message->opiloId(), $message->getFrom(), $message->getTo(), $message->getText(), and $message->getReceivedAt() and store them in your database
            $minId = max($minId, $message->getOpiloId() + 1);
        }
    } else {
        //no new SMS
        //Store $minId in your database for later use of this while loop! You don't need to start from 0 tomorrow!
        break;
    }
}
```

## Checking the Delivery Status of Sent Messages
```php
$opiloIds = $yourDatabaseRepository->getArrayOfOpiloIdsOfMessagesSentViaSendSMSFunction();
$response = $client->checkStatus($opiloIds);
foreach ($response->getStatusArray() as $opiloId => $status) {
    //process and store the status code $status->getCode() for the SMS with Id $opiloId
    //Take a look at constants in OpiloClient\Response\Status class and their meanings
}
```

## Getting Your SMS Credit
```php
$numberOfSMSYouCanSendBeforeNeedToCharge = $client->getCredit()->getSmsPageCount();
```

## Exception Handling
All the functions in HttpClient may throw CommunicationException if the credentials or configurations are invalid, or if there is a network or server error.
Prepare to catch the exceptions appropriately.

```php
use OpiloClient\Response\CommunicationException;
...
try {
    ...
    $client->sendSMS(...);
    ...
} catch (CommunicationException $e) {
    //process the exception by comparing $e->getCode() against constants defined in CommunicationException class.
}
```


## Laravel support
To use web service in Laravel, register `OpiloClient\Laravel\OpiloServiceProvider` in your `config/app.php`.
```php
    'providers' => [
        // Add this to end of 'providers' array
        OpiloClient\Laravel\OpiloServiceProvider::class,
    ]
```
You can also add the facade to use web service more conveniently.
```php
    'aliases' => [
        // Add this to end of 'aliases' array
        'Opilo' => OpiloClient\Laravel\HttpClient::class,
    ]
```
To publish opilo config file, run `php artisan vendor:publish --provider="OpiloClient\Laravel\OpiloServiceProvider"`.
Put variables `OPILO_WS_USERNAME` and `OPILO_WS_PASSWORD` into your `.env` file.