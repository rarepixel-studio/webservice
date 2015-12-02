<?php

namespace OpiloClientTest\Integration;

use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SMSId;
use OpiloClient\V1\HttpClient;
use PHPUnit_Framework_TestCase;

class LegacyAPIExceptionTest extends PHPUnit_Framework_TestCase
{
    public function test401()
    {
        $this->setExpectedException(CommunicationException::class, 'Authentication Failed', CommunicationException::AUTH_ERROR);
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), 'wrong_password'));
        $client->sendSMS('3000', '9130000000', 'text');
    }

    /**
     * @group 403
     */
    public function test403()
    {
        $this->setExpectedException(CommunicationException::class, 'Forbidden [Web-service is disabled]', CommunicationException::FORBIDDEN);
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME_WS_DISABLED'), getenv('OPILO_PASSWORD_WS_DISABLED')));
        $client->sendSMS('3000', '9130000000', 'text');
    }

    public function testInvalidFrom()
    {
        $this->setExpectedException(CommunicationException::class, 'Invalid From', CommunicationException::INVALID_INPUT);
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $client->sendSMS('asdf', '9130000000', 'text');
    }

    public function testInvalidTo()
    {
        $this->setExpectedException(CommunicationException::class, 'Invalid To', CommunicationException::INVALID_INPUT);
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $client->sendSMS(getenv('PANEL_LINE'), 'junk', 'text');
    }

    public function testMixedValidInvalidToArray()
    {
        $client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $response = $client->sendSMS(getenv('PANEL_LINE'), 'junk,' . getenv('DESTINATION'), 'text');
        $this->assertCount(2, $response);
        $this->assertInstanceOf(SendError::class, $response[0]);
        $this->assertEquals(SendError::ERROR_INVALID_DESTINATION ,$response[0]->getError());
        $this->assertInstanceOf(SMSId::class, $response[1]);
    }
}