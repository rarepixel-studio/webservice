<?php

namespace OpiloClient\V2;

use DateTime;
use GuzzleHttp\Client;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Request\OutgoingSMS;
use OpiloClient\Response\CheckStatusResponse;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;

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
        $this->client  = $config->getHttpClient(ConnectionConfig::VERSION_2);
        $this->account = $account;
    }

    /**
     * @param OutgoingSMS|OutgoingSMS[] $messages
     *
     * @throws CommunicationException
     *
     * @return SendSMSResponse[]|SMSId[]|SendError[]
     */
    abstract public function sendSMS($messages);

    /**
     * @param int $minId
     * @param DateTime|string|null $minReceivedAt
     * @param string $read
     *
     * @see Inbox::INBOX_ALL, Inbox::INBOX_READ, Inbox::INBOX_NOT_READ
     *
     * @param string|null $line_number
     *
     * @return Inbox
     *
     * @throws CommunicationException
     */
    abstract public function checkInbox($minId = 0, $minReceivedAt = null, $read = Inbox::INBOX_ALL, $line_number = null);

    /**
     * @param int|int[] $opiloIds
     *
     * @throws CommunicationException
     *
     * @return CheckStatusResponse
     */
    abstract public function checkStatus($opiloIds);

    /**
     * @throws CommunicationException
     *
     * @return Credit
     */
    abstract public function getCredit();
}
