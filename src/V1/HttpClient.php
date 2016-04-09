<?php

namespace OpiloClient\V1;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;
use OpiloClient\V1\Bin\Out;
use OpiloClient\V1\Bin\Parser;

/**
 * @deprecated
 */
class HttpClient
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var Client
     */
    protected $client;

    private $clientVersion;

    /**
     * @param string             $username
     * @param string             $password
     * @param null|string|Client $serverBaseUrl
     */
    public function __construct($username, $password, $serverBaseUrl = null)
    {
        $this->account = new Account($username, $password);
        $version = ClientInterface::VERSION;
        $this->clientVersion = $version[0];
        if ($serverBaseUrl instanceof Client) {
            $this->client = $serverBaseUrl;
        } else {
            $this->client = (new ConnectionConfig($serverBaseUrl))->getHttpClient(ConnectionConfig::VERSION_1);
        }
    }

    /**
     * @param string       $from
     * @param string|array $to
     * @param string       $text
     *
     * @throws CommunicationException
     *
     * @return SendError[]|SendSMSResponse[]|SMSId[]
     */
    public function sendSMS($from, $to, $text)
    {
        if (!is_array($to)) {
            $to = [$to];
        }
        $to = implode(',', $to);

        $options = [
            'query' => Out::attachAuth($this->account, [
                'from' => $from,
                'to'   => $to,
                'text' => $text,
            ]),
        ];

        if ($this->clientVersion == '5') {
            $request = $this->client->createRequest('GET', 'httpsend', $options);
            $response = Out::send($this->client, $request);
        } else {
            $request = new Request('GET', 'httpsend');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareSendResponse($response);
    }

    /**
     * @param int         $fromId
     * @param string|null $fromDate
     * @param int         $read
     * @param string|null $number
     * @param int         $count
     *
     * @throws CommunicationException
     *
     * @return Inbox
     */
    public function checkInbox($fromId = 0, $fromDate = null, $read = 0, $number = null, $count = Inbox::PAGE_LIMIT)
    {
        $query = [];
        if ($fromId) {
            $query['from_id'] = $fromId;
        }
        if ($fromDate) {
            $query['from_date'] = $fromDate;
        }
        if ($read) {
            $query['read'] = 1;
        }
        if ($number) {
            $query['number'] = $number;
        }
        if ($count) {
            $query[$count] = $count;
        }

        $options = [
            'query' => Out::attachAuth($this->account, $query),
        ];

        if ($this->clientVersion == '5') {
            $request = $this->client->createRequest('GET', 'getAllMessages', $options);
            $response = Out::send($this->client, $request);
        } else {
            $request = new Request('GET', 'getAllMessages');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareInbox($response);
    }

    /**
     * @param int $from  offset from
     * @param int $count
     *
     * @throws CommunicationException
     *
     * @return Inbox
     */
    public function receive($from = 0, $count = Inbox::PAGE_LIMIT)
    {
        $query = [];
        if ($from) {
            $query['from'] = $from;
        }
        if ($count) {
            $query['count'] = $count;
        }

        $options = [
            'query' => Out::attachAuth($this->account, $query),
        ];

        if ($this->clientVersion == '5') {
            $request = $this->client->createRequest('GET', 'recieve', $options);
            $response = Out::send($this->client, $request);
        } else {
            $request = new Request('GET', 'recieve');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareInbox($response);
    }

    /**
     * @param int|int[] $opiloIds
     *
     * @throws CommunicationException
     *
     * @return Status[]
     */
    public function checkStatus($opiloIds)
    {
        if (!is_array($opiloIds)) {
            $opiloIds = [$opiloIds];
        }

        $options = [
            'query' => Out::attachAuth($this->account, ['ids' => $opiloIds]),
        ];

        if ($this->clientVersion == '5') {
            $request = $this->client->createRequest('GET', 'getStatus', $options);
            $response = Out::send($this->client, $request);
        } else {
            $request = new Request('GET', 'getStatus');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareStatusArray($opiloIds, $response);
    }

    /**
     * @throws CommunicationException
     *
     * @return Credit
     */
    public function getCredit()
    {
        $options = [
            'query' => Out::attachAuth($this->account, []),
        ];

        if ($this->clientVersion == '5') {
            $request = $this->client->createRequest('GET', 'getCredit', $options);
            $response = Out::send($this->client, $request);
        } else {
            $request = new Request('GET', 'getCredit');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareCredit($response);
    }
}
