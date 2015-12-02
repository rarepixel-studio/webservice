<?php

namespace OpiloClientTest\Integration;

use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Request\IncomingSMS;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;
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
        $this->client = new HttpClient(new ConnectionConfig(getenv('OPILO_URL')), new Account(getenv('OPILO_USERNAME'), getenv('OPILO_PASSWORD')));
    }

    public function testGetCredit()
    {
        $credit = $this->client->getCredit();
        $this->assertInstanceOf(Credit::class, $credit);
        $this->assertTrue(is_numeric($credit->getSmsPageCount()));
    }

    public function testSendSingleSMS()
    {
        $initCredit = $this->client->getCredit()->getSmsPageCount();
        $text = 'Legacy::testSendSingleSMS()';
        $response = $this->client->sendSMS(getenv('PANEL_LINE'), getenv('DESTINATION'), $text);
        $this->assertCount(1, $response);
        $this->assertInstanceOf(SMSId::class, $response[0]);
        $status = $this->client->checkStatus($response[0]->getId());
        $this->assertCount(1, $status);
        $this->assertInstanceOf(Status::class, $status[0]);
        $finalCredit = $this->client->getCredit()->getSmsPageCount();
        $this->assertLessThanOrEqual(1, $initCredit - $finalCredit);
    }

    public function testSendMultipleSMS()
    {
        $initCredit = $this->client->getCredit()->getSmsPageCount();
        $to = [];
        $text = 'legacy::testSendMultipleSMS()';
        for($i = 0; $i < 10; $i++) {
            $to[] = getenv('DESTINATION');
        }
        $response = $this->client->sendSMS(getenv('PANEL_LINE'), $to, $text);
        $this->assertCount(10, $response);
        $ids = [];
        foreach ($response as $id) {
            $this->assertInstanceOf(SMSId::class, $id);
            $ids[] = $id->getId();
        }

        $status = $this->client->checkStatus($ids);
        $this->assertCount(10, $status);
        foreach ($status as $stat) {
            $this->assertInstanceOf(Status::class, $stat);
        }

        $finalCredit = $this->client->getCredit()->getSmsPageCount();
        $this->assertLessThanOrEqual(10, $initCredit - $finalCredit);
    }

    public function testCheckInbox()
    {
        $response = $this->client->checkInbox(0);
        $this->assertInstanceOf(Inbox::class, $response);
        $response = $response->getMessages();
        $this->assertTrue(is_array($response));
        $this->assertLessThanOrEqual(Inbox::PAGE_LIMIT, count($response));
        foreach ($response as $sms) {
            $this->assertInstanceOf(IncomingSMS::class, $sms);
        }
    }

    public function testCheckInboxFromDate()
    {
        $response = $this->client->checkInbox(0, '2015-08-02', 1);
        $this->assertInstanceOf(Inbox::class, $response);
        $response = $response->getMessages();
        $this->assertTrue(is_array($response));
        $this->assertLessThanOrEqual(Inbox::PAGE_LIMIT, count($response));
        foreach ($response as $sms) {
            $this->assertInstanceOf(IncomingSMS::class, $sms);
        }
    }

    public function testCheckInboxIgnoresBadFromDate()
    {
        $firstResponse = $this->client->checkInbox(0, null, 1);

        $response = $this->client->checkInbox(0, '20', 1);
        $this->assertCount(count($firstResponse->getMessages()), $response->getMessages());
        $this->assertInstanceOf(Inbox::class, $response);
        $response = $response->getMessages();
        $this->assertTrue(is_array($response));
        $this->assertLessThanOrEqual(Inbox::PAGE_LIMIT, count($response));
        foreach ($response as $sms) {
            $this->assertInstanceOf(IncomingSMS::class, $sms);
        }
    }

    public function testReceive()
    {
        $firstResponse = $this->client->receive(0, Inbox::PAGE_LIMIT)->getMessages();
        $this->assertGreaterThan($firstResponse[1]->getOpiloId(), $firstResponse[0]->getOpiloId());
        $secondResponse = $this->client->receive(1, Inbox::PAGE_LIMIT)->getMessages();
        $this->assertEquals($firstResponse[1]->getOpiloId(), $secondResponse[0]->getOpiloId());
    }
}