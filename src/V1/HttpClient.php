<?php

namespace OpiloClient\V1;

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Response\CommunicationException;
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
     * @return \OpiloClient\Response\SendError[]|\OpiloClient\Response\SendSMSResponse[]|\OpiloClient\Response\SMSId[]
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

        return $this->prepareSendResponse($response);
    }

    public function checkInbox($minId = 0)
    {
    }

    public function checkStatus($opiloIds)
    {
    }

    public function getCredit()
    {
        $request = $this->client->createRequest('GET', 'getCredit', [
            'query' => Out::attachAuth($this->account, []),
        ]);
        $response = Out::send($this->client, $request);

        return Parser::prepareGetCreditResponse($response);
    }

    protected function prepareSendResponse(ResponseInterface $response)
    {
        return Parser::getRawResponseBody($response);
    }
}