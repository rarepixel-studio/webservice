<?php

namespace OpiloClientTest\Integration;

use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\ValidationException;
use OpiloClient\V2\HttpClient;
use PHPUnit_Framework_TestCase;

class HttpClientExceptionTest extends PHPUnit_Framework_TestCase
{
    public function test401()
    {
        $this->setExpectedException(CommunicationException::class, 'Authentication Failed', CommunicationException::AUTH_ERROR);
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), 'wrong_password'));
        $client->getCredit();
    }

    public function test403()
    {
        $this->setExpectedException(CommunicationException::class, 'Forbidden [Web-service is disabled]', CommunicationException::FORBIDDEN);
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME_WS_DISABLED'), getenv('OPILO_PASSWORD_WS_DISABLED')));
        $client->getCredit();
    }

    public function test422()
    {
        $this->setExpectedException(ValidationException::class, 'Input Validation Failed', CommunicationException::INVALID_INPUT);
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $client->checkStatus(['string']);
    }

    public function test422Errors()
    {
        $failed = false;
        try {
            $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
            $client->checkStatus(['string']);
        } catch(ValidationException $e) {
            $failed = true;
            $errors = $e->getErrors();
            $this->assertCount(1, $errors);
            $this->assertArrayHasKey('Integer', $errors['ids.0']);
        }
        $this->assertTrue($failed);
    }
}