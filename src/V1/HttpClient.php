<?php

namespace OpiloClient\V1;

use GuzzleHttp\Client;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;

abstract class HttpClient
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(ConnectionConfig $config, Account $account)
    {
        $this->client  = $config->getHttpClient(ConnectionConfig::VERSION_1);
        $this->account = $account;
    }

    /**
     * @param string $from
     * @param string|array $to
     * @param string $text
     *
     * @return SendError[]|SendSMSResponse[]|SMSId[]
     *
     * @throws CommunicationException
     */
    abstract public function sendSMS($from, $to, $text);

    /**
     * @param int $fromId
     * @param string|null $fromDate
     * @param int $read
     * @param string|null $number
     * @param int $count
     *
     * @return Inbox
     *
     * @throws CommunicationException
     */
    abstract public function checkInbox($fromId = 0, $fromDate = null, $read = 0, $number = null, $count = Inbox::PAGE_LIMIT);

    /**
     * @param int $from offset from
     * @param int $count
     *
     * @deprecated
     *
     * @return Inbox
     */
    abstract public function receive($from = 0, $count = Inbox::PAGE_LIMIT);

    /**
     * @param int|int[] $opiloIds
     *
     * @return Status[]
     */
    abstract public function checkStatus($opiloIds);

    /**
     * @return Credit
     *
     * @throws CommunicationException
     */
    abstract public function getCredit();
}