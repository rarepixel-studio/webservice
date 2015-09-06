<?php

namespace OpiloClient\V1;

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
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

    public function checkInbox($minId = 0)
    {
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