<?php

namespace OpiloClient\V2;

use GuzzleHttp\Client;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Request\IncomingSMS;
use OpiloClient\Request\OutgoingSMS;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;
use OpiloClient\V2\Bin\Parser;
use OpiloClient\V2\Bin\Out;

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
        $this->client = $config->getHttpClient(ConnectionConfig::VERSION_2);
        $this->account = $account;
    }

    /**
     * @param OutgoingSMS|OutgoingSMS[] $messages
     * @throws CommunicationException
     * @return SendSMSResponse[]|SMSId[]|SendError[]
     */
    public function sendSMS($messages)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        $request = $this->client->createRequest('POST', 'sms/send', [
            'json' => Out::attachAuth($this->account, Out::SMSArrayToSendRequestBody($messages)),
        ]);
        $response = Out::send($this->client, $request);

        return Parser::prepareSendResponse($response);
    }

    /**
     * @param int $minId
     * @throws CommunicationException
     * @return IncomingSMS[] an array of maximum 50 messages with id >= minId
     */
    public function checkInbox($minId = 0)
    {
        $response = Out::send($this->client, $this->client->createRequest('GET', 'inbox', [
            'query' => Out::attachAuth($this->account, ['min_id' => $minId]),
        ]));

        return Parser::prepareIncomingSMS($response);
    }

    /**
     * @param int|int[] $opiloIds
     * @throws CommunicationException
     * @return Status[]
     */
    public function checkStatus($opiloIds)
    {
        if (!is_array($opiloIds)) {
            $opiloIds = [$opiloIds];
        }

        $response = Out::send($this->client, $this->client->createRequest('GET', 'sms/status', [
            'query' => Out::attachAuth($this->account, ['ids' => $opiloIds]),
        ]));

        return Parser::prepareStatusArray($response);
    }

    /**
     * @throws CommunicationException
     * @return string
     */
    public function getCredit()
    {
        $response = Out::send($this->client, $this->client->createRequest('GET','credit', [
            'query' => Out::attachAuth($this->account, []),
        ]));
        return Parser::prepareCredit($response);
    }
}