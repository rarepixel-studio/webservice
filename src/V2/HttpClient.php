<?php

namespace OpiloClient\V2;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
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
use OpiloClient\V2\Bin\Out;
use OpiloClient\V2\Bin\Parser;

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
     * @param string $username
     * @param string $password
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
            $this->client = (new ConnectionConfig($serverBaseUrl))->getHttpClient(ConnectionConfig::VERSION_2);
        }
    }

    /**
     * @param OutgoingSMS|OutgoingSMS[] $messages
     *
     * @throws CommunicationException
     *
     * @return SendSMSResponse[]|SMSId[]|SendError[]
     */
    public function sendSMS($messages)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $options = Out::attachAuth($this->account, Out::SMSArrayToSendRequestBody($messages));
        if ($this->clientVersion == '5') {
            $request = $this->client->createRequest('POST', 'sms/send', ['json' => $options]);
            $response = Out::send($this->client, $request);
        } else {
            $request = new Request('POST', 'sms/send');
            $response = Out::send($this->client, $request, ['query' => $options]);
        }

        return Parser::prepareSendResponse($response);
    }

    /**
     * @param int                  $minId
     * @param DateTime|string|null $minReceivedAt
     * @param string               $read
     *
     * @see Inbox::INBOX_ALL, Inbox::INBOX_READ, Inbox::INBOX_NOT_READ
     *
     * @param string|null $line_number
     *
     * @throws CommunicationException
     *
     * @return Inbox
     */
    public function checkInbox($minId = 0, $minReceivedAt = null, $read = Inbox::INBOX_ALL, $line_number = null)
    {
        $query = [];

        if ($minId) {
            $query['min_id'] = $minId;
        }

        if ($minReceivedAt) {
            if ($minReceivedAt instanceof DateTime) {
                $query['min_received_at'] = $minReceivedAt->format('Y-m-d H:i:s');
            } else {
                $query['min_received_at'] = $minReceivedAt;
            }
        }

        if ($read != Inbox::INBOX_ALL) {
            $query['read'] = $read;
        }

        if ($line_number) {
            $query['line_number'] = $line_number;
        }

        $options = ['query' => Out::attachAuth($this->account, $query)];
        if ($this->clientVersion == '5') {
            $response = Out::send($this->client, $this->client->createRequest('GET', 'inbox', $options));
        } else {
            $request = new Request('GET', 'inbox');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareIncomingSMS($response);
    }

    /**
     * @param int|int[] $opiloIds
     *
     * @throws CommunicationException
     *
     * @return CheckStatusResponse
     */
    public function checkStatus($opiloIds)
    {
        if (!is_array($opiloIds)) {
            $opiloIds = [$opiloIds];
        }

        $options = ['query' => Out::attachAuth($this->account, ['ids' => $opiloIds])];
        if ($this->clientVersion == '5') {
            $response = Out::send($this->client, $this->client->createRequest('GET', 'sms/status', $options));
        } else {
            $request = new Request('GET', 'sms/status');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareStatusArray($response);
    }

    /**
     * @throws CommunicationException
     *
     * @return Credit
     */
    public function getCredit()
    {
        $options = ['query' => Out::attachAuth($this->account, [])];
        if ($this->clientVersion == '5') {
            $response = Out::send($this->client, $this->client->createRequest('GET', 'credit', $options));
        } else {
            $request = new Request('GET', 'credit');
            $response = Out::send($this->client, $request, $options);
        }

        return Parser::prepareCredit($response);
    }
}
