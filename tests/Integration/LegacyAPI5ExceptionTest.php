<?php

namespace OpiloClientTest\Integration;

use GuzzleHttp\ClientInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SMSId;
use OpiloClient\V1\HttpClient5;
use PHPUnit_Framework_TestCase;

class LegacyAPI5ExceptionTest extends PHPUnit_Framework_TestCase
{
    private $guzzleVersion;

    public function setUp()
    {
        parent::setUp();
        $this->guzzleVersion = (string)ClientInterface::VERSION[0];
    }

    public function test401()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $this->setExpectedException(CommunicationException::class, 'Authentication Failed', CommunicationException::AUTH_ERROR);
        $client = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), 'wrong_password'));
        $client->sendSMS('3000', '9130000000', 'text');
    }

    /**
     * @group 403
     */
    public function test403()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $this->setExpectedException(CommunicationException::class, 'Forbidden [Web-service is disabled]', CommunicationException::FORBIDDEN);
        $client = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME_WS_DISABLED'), getenv('OPILO_PASSWORD_WS_DISABLED')));
        $client->sendSMS('3000', '9130000000', 'text');
    }

    public function testInvalidFrom()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $this->setExpectedException(CommunicationException::class, 'Invalid From', CommunicationException::INVALID_INPUT);
        $client = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $client->sendSMS('asdf', '9130000000', 'text');
    }

    public function testInvalidTo()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $this->setExpectedException(CommunicationException::class, 'Invalid To', CommunicationException::INVALID_INPUT);
        $client = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $client->sendSMS(getenv('PANEL_LINE'), 'junk', 'text');
    }

    public function testMixedValidInvalidToArray()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $client = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $response = $client->sendSMS(getenv('PANEL_LINE'), 'junk,' . getenv('DESTINATION'), 'text');
        $this->assertCount(2, $response);
        $this->assertInstanceOf(SendError::class, $response[0]);
        $this->assertEquals(SendError::ERROR_INVALID_DESTINATION, $response[0]->getError());
        $this->assertInstanceOf(SMSId::class, $response[1]);
    }
}
