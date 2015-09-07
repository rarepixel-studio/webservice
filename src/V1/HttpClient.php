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
        $this->client = $config->getHttpClient(ConnectionConfig::VERSION_1);
        $this->account = $account;
    }

    /**
     * @param string $from
     * @param string|array $to
     * @param string $text
     * @return SendError[]|SendSMSResponse[]|SMSId[]
     * @throws CommunicationException
     */
    public function sendSMS($from, $to, $text)
    {
        if (!is_array($to)) {
            $to = [$to];
        }
        $to = join(',', $to);

        $request = $this->client->createRequest('GET', 'httpsend', [
            'query' => Out::attachAuth($this->account, [
                'from' => $from,
                'to' => $to,
                'text' => $text]),
            ]
        );
        $response = Out::send($this->client, $request);

        return Parser::prepareSendResponse($response);
    }

    /**
     * @param int $fromId
     * @param string|null $fromDate
     * @param int $read
     * @param string|null $number
     * @param int $count
     * @return Inbox
     * @throws CommunicationException
     */
    public function checkInbox($fromId = 0, $fromDate = null, $read = 0, $number = null, $count = Inbox::PAGE_LIMIT)
    {
        $query = [];
        if($fromId) {
            $query['from_id'] = $fromId;
        }
        if($fromDate) {
            $query['from_date'] = $fromDate;
        }
        if($read) {
            $query['read'] = 1;
        }
        if($number) {
            $query['number'] = $number;
        }
        if($count) {
            $query[$count] = $count;
        }
        $request = $this->client->createRequest('GET', 'getAllMessages', [
            'query' => Out::attachAuth($this->account, $query),
        ]);
        $response = Out::send($this->client, $request);

        return Parser::prepareInbox($response);
    }

    /**
     * @param int|int[] $opiloIds
     * @return Status[]
     */
    public function checkStatus($opiloIds)
    {
        if(! is_array($opiloIds)) {
            $opiloIds = [$opiloIds];
        }
        $request = $this->client->createRequest('GET', 'getStatus', [
                'query' => Out::attachAuth($this->account, [
                    'ids' => $opiloIds,
                ])]);
        $response = Out::send($this->client, $request);

        return Parser::prepareStatusArray($opiloIds, $response);
    }

    /**
     * @return Credit
     * @throws CommunicationException
     */
    public function getCredit()
    {
        $request = $this->client->createRequest('GET', 'getCredit', [
            'query' => Out::attachAuth($this->account, []),
        ]);
        $response = Out::send($this->client, $request);

        return Parser::prepareCredit($response);
    }
}