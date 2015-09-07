<?php

namespace OpiloClientTest\Integration;

use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
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
        $account = new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD'));
        $config = new ConnectionConfig(getenv('OPILO_URL'));
        $client = $config->getHttpClient();
        $response = $client->get('sms/status', [
            'query' => [
                'ids' => ['abcd'],
                'username' => $account->getUserName(),
                'password' => $account->getPassword()]
        ]);
        $this->assertEquals('422',$response->getStatusCode());
    }
}