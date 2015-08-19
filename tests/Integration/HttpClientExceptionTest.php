<?php

namespace OpiloClientTest\Integration;

use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\HttpClient;
use OpiloClient\Response\CommunicationException;
use PHPUnit_Framework_TestCase;

class HttpClientExceptionTest extends PHPUnit_Framework_TestCase
{
    public function test401()
    {
        $this->setExpectedException(CommunicationException::class, 'Authentication Failed', CommunicationException::AUTH_ERROR);
        $client = new HttpClient(new ConnectionConfig(getenv('POILO_URL'), '2'), new Account(getenv('OPILO_USERNAME'), 'wrong_password'));
        $client->getCredit();
    }

    public function test403()
    {
        $this->setExpectedException(CommunicationException::class, 'Forbidden [Web-service is disabled]', CommunicationException::FORBIDDEN);
        $client = new HttpClient(new ConnectionConfig(getenv('POILO_URL'), '2'), new Account(getenv('OPILO_USERNAME_WS_DISABLED'), getenv('OPILO_PASSWORD_WS_DISABLED')));
        $client->getCredit();
    }
}