# Webservice Client for opilo.com panel

In order to send and receive SMS via opilo.com panel, you should first create an instance object of class OpiloClient\V2\HttpClient.
For that, first you need to configure your webservice in [the configuration page](http://bpanel.opilo.com/api).
## Create a Client Object
```php
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\V2\HttpClient;
...
$config = new ConnectionConfig('http://bpanel.opilo.com');
$account = new Account('YOUR_WEBSERVICE_USERNAME', 'YOUR_WEBSERVICE_PASSWORD');
$client = new HttpClient($config, $account);
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

### Parsing The Return Value of sendSMS()
```php
use OpiloClient\Response\SMSId;
use OpiloClient\Response\SendError;
...
for ($i = 0; $i < count($response); $i++) {
    if ($response[$i] instanceof SMSId) {
        //store $response[$i]->id as the id of $messages[$i] in your database and schedule for checking status if needed
    } else //$response[$i] instanceof SendError {
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
