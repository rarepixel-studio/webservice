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
                'text' => $text,]),
        ];

        if (substr(ClientInterface::VERSION, 0, 1) == '5') {
            $request  = $this->client->createRequest('GET', 'httpsend', $options);
            $response = Out::send($this->client, $request);
        } elseif (substr(ClientInterface::VERSION, 0, 1) == '6') {
            $request  = new Request('GET', 'httpsend');
            $response = Out::send($this->client, $request, $options);
        } else {
            throw new \Exception('unsupported guzzle version');
        }

        return Parser::prepareSendResponse($response);
    }

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

        if (substr(ClientInterface::VERSION, 0, 1) == '5') {
            $request  = $this->client->createRequest('GET', 'getAllMessages', $options);
            $response = Out::send($this->client, $request);
        } elseif (substr(ClientInterface::VERSION, 0, 1) == '6') {
            $request  = new Request('GET', 'getAllMessages');
            $response = Out::send($this->client, $request, $options);
        } else {
            throw new \Exception('unsupported guzzle version');
        }

        return Parser::prepareInbox($response);
    }

    /**
     * @param int $from offset from
     * @param int $count
     *
     * @deprecated
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

        if (substr(ClientInterface::VERSION, 0, 1) == '5') {
            $request  = $this->client->createRequest('GET', 'recieve', $options);
            $response = Out::send($this->client, $request);
        } elseif (substr(ClientInterface::VERSION, 0, 1) == '6') {
            $request  = new Request('GET', 'recieve');
            $response = Out::send($this->client, $request, $options);
        } else {
            throw new \Exception('unsupported guzzle version');
        }


        return Parser::prepareInbox($response);
    }

    /**
     * @param int|int[] $opiloIds
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

        if (substr(ClientInterface::VERSION, 0, 1) == '5') {
            $request  = $this->client->createRequest('GET', 'getStatus', $options);
            $response = Out::send($this->client, $request);
        } elseif (substr(ClientInterface::VERSION, 0, 1) == '6') {
            $request  = new Request('GET', 'getStatus');
            $response = Out::send($this->client, $request, $options);
        } else {
            throw new \Exception('unsupported guzzle version');
        }

        return Parser::prepareStatusArray($opiloIds, $response);
    }

    /**
     * @return Credit
     *
     * @throws CommunicationException
     */
    public function getCredit()
    {
        $options = [
            'query' => Out::attachAuth($this->account, []),
        ];

        if (substr(ClientInterface::VERSION, 0, 1) == '5') {
            $request  = $this->client->createRequest('GET', 'getCredit', $options);
            $response = Out::send($this->client, $request);
        } elseif (substr(ClientInterface::VERSION, 0, 1) == '6') {
            $request  = new Request('GET', 'getCredit');
            $response = Out::send($this->client, $request, $options);
        } else {
            throw new \Exception('unsupported guzzle version');
        }

        return Parser::prepareCredit($response);
    }
}
