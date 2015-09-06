<?php

namespace OpiloClientTest\Integration;

use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\V1\HttpClient;
use PHPUnit_Framework_TestCase;

class LegacyAPIBasicTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpClient
     */
    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = new HttpClient(new ConnectionConfig(getenv('POILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
    }

    public function testGetCredit()
    {
        $credit = $this->client->getCredit();
        $this->assertTrue(is_numeric($credit));
    }
}