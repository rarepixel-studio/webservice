<?php

namespace OpiloClientTest\Integration;

use GuzzleHttp\ClientInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Request\OutgoingSMS;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\ValidationException;
use OpiloClient\V2\HttpClient5;
use PHPUnit_Framework_TestCase;

class HttpClient5ExceptionTest extends PHPUnit_Framework_TestCase
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
        $client->getCredit();
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
        $client->getCredit();
    }

    public function test422()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $this->setExpectedException(ValidationException::class, 'Input Validation Failed', CommunicationException::INVALID_INPUT);
        $client = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $client->checkStatus(['string']);
    }

    public function test422Errors()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $failed = false;
        try {
            $client = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
            $client->checkStatus(['string']);
        } catch (ValidationException $e) {
            $failed = true;
            $errors = $e->getErrors();
            $this->assertCount(1, $errors);
            $this->assertArrayHasKey('Integer', $errors['ids.0']);
        }
        $this->assertTrue($failed);
    }

    public function testSendInvalidSMS()
    {
        if ($this->guzzleVersion !== '5') {
            return;
        }
        $client   = new HttpClient5(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
        $messages = [
            new OutgoingSMS('abcd', getenv('DESTINATION'), 'invalid from'),
            new OutgoingSMS(getenv('PANEL_LINE'), 'abcd', 'invalid to'),
            new OutgoingSMS('3000', getenv('DESTINATION'), 'unauthorized from'),
            new OutgoingSMS(getenv('PANEL_LINE'), getenv('DESTINATION'), 'authorized from'),
        ];

        $response = $client->sendSMS($messages);
        $this->assertCount(4, $response);
        $this->assertInstanceOf(SendError::class, $response[0]);
        $this->assertInstanceOf(SendError::class, $response[1]);
        $this->assertInstanceOf(SendError::class, $response[2]);
        $this->assertInstanceOf(SMSId::class, $response[3]);
        $this->assertSame(SendError::ERROR_RESOURCE_NOT_FOUND, $response[0]->getError());
        $this->assertSame(SendError::ERROR_INVALID_DESTINATION, $response[1]->getError());
        $this->assertSame(SendError::ERROR_RESOURCE_NOT_FOUND, $response[2]->getError());
    }
}
