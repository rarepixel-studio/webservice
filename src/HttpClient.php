<?php

namespace OpiloClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Configs\ConnectionConfig;
use OpiloClient\Request\IncomingSMS;
use OpiloClient\Request\OutgoingSMS;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;

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
        $this->client = $config->getHttpClient();
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
            'json' => $this->attachAuth($this->SMSArrayToSendRequestBody($messages)),
        ]);
        $response = $this->send($request);

        return $this->prepareSendResponse($response);
    }

    /**
     * @param int $minId
     * @throws CommunicationException
     * @return IncomingSMS[] an array of maximum 50 messages with id >= minId
     */
    public function checkInbox($minId = 0)
    {
        $response = $this->send($this->client->createRequest('GET', 'inbox', [
            'query' => $this->attachAuth(['min_id' => $minId]),
        ]));

        return $this->prepareIncomingSMS($response);
    }

    /**
     * @param int|int[] $opiloIds
     * @throws CommunicationException
     * @return Response\Status[]
     */
    public function checkStatus($opiloIds)
    {
        if (!is_array($opiloIds)) {
            $opiloIds = [$opiloIds];
        }

        $response = $this->send($this->client->createRequest('GET', 'sms/status', [
            'query' => $this->attachAuth(['ids' => $opiloIds]),
        ]));

        return $this->prepareStatusArray($response);
    }

    /**
     * @throws CommunicationException
     * @return string
     */
    public function getCredit()
    {
        $response = $this->send($this->client->createRequest('GET','credit', [
            'query' => $this->attachAuth([]),
        ]));
        return $this->prepareCredit($response);
    }

    /**
     * @param OutgoingSMS[] $messages
     * @return array
     */
    protected function SMSArrayToSendRequestBody($messages)
    {
        $array = [
            'from' => [],
            'to' => [],
            'text' => [],
            'user_defined_id' => [],
        ];

        foreach ($messages as $message) {
            $array['from'][] = $message->getFrom();
            $array['to'][] = $message->getTo();
            $array['text'][] = $message->getText();
            $array['id'][] = $message->getUserDefinedId();
        }
        return $array;
    }

    /**
     * @param ResponseInterface $response
     * @return SendSMSResponse[]
     * @throws CommunicationException
     */
    protected function prepareSendResponse(ResponseInterface $response)
    {
        $rawResponse = $this->getRawResponseBody($response);

        return $this->makeSendResponseArray($rawResponse);
    }

    /**
     * @param string $rawResponse
     * @return SendSMSResponse[]
     * @throws CommunicationException
     */
    protected function makeSendResponseArray($rawResponse)
    {
        $array = json_decode($rawResponse, true);
        if (is_null($array) || !is_array($array)) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        $prepared = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                throw new CommunicationException("Unprocessable Response item: $rawResponse",
                    CommunicationException::UNPROCESSABLE_RESPONSE_ITEM);
            }
            if (array_key_exists('error', $item)) {
                $prepared[] = new SendError($item['error']);
            } elseif (array_key_exists('id', $item)) {
                $prepared[] = new SMSId($item['id']);
            } else {
                throw new CommunicationException("Unprocessable Response item: $rawResponse",
                    CommunicationException::UNPROCESSABLE_RESPONSE_ITEM);
            }
        }

        return $prepared;
    }

    /**
     * @param ResponseInterface $response
     * @throws CommunicationException
     * @return Status[]
     */
    protected function prepareStatusArray(ResponseInterface $response)
    {
        $rawResponse = $this->getRawResponseBody($response);
        return $this->makeStatusArray($rawResponse);
    }

    /**
     * @param string $rawResponse
     * @return Status[]
     * @throws CommunicationException
     */
    protected function makeStatusArray($rawResponse)
    {
        $array = json_decode($rawResponse, true);
        if (is_null($array) || !is_array($array)) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        $prepared = [];

        foreach ($array as $item) {
            if (!is_numeric($item)) {
                throw new CommunicationException("Unprocessable Response item: $rawResponse",
                    CommunicationException::UNPROCESSABLE_RESPONSE_ITEM);
            }
            $prepared[] = new Status((int)$item);
        }

        return $prepared;
    }

    /**
     * @param ResponseInterface $response
     * @return Response\Status[]
     * @throws CommunicationException
     * @return string
     */
    protected function prepareCredit(ResponseInterface $response)
    {
        $rawResponse = $this->getRawResponseBody($response);
        $array = json_decode($rawResponse, true);
        if(! is_array($array) || ! array_key_exists('credit', $array)) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }
        return $array['credit'];
    }

    /**
     * @param ResponseInterface $response
     * @throws CommunicationException
     * @return IncomingSMS[]
     */
    protected function prepareIncomingSMS(ResponseInterface $response)
    {
        $rawResponse = $this->getRawResponseBody($response);

        $array = json_decode($rawResponse, true);
        if (is_null($array) || !is_array($array)) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        $prepared = [];

        foreach ($array as $id => $item) {
            $prepared[] = new IncomingSMS($id, $item['from'], $item['to'], $item['text'],
                \DateTime::createFromFormat('Y-m-d H:i:s', $item['time']));
        }

        return $prepared;
    }

    /**
     * @param ResponseInterface $response
     * @return string
     * @throws CommunicationException
     */
    protected function getRawResponseBody(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $rawResponse = $response->getBody()->getContents();

        if ($statusCode != 200) {
            throw new CommunicationException("StatusCode: $statusCode, Contents: $rawResponse",
                CommunicationException::HTTP_ERROR);
        }
        return $rawResponse;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws CommunicationException
     */
    protected function send(RequestInterface $request)
    {
        try{
            return $this->client->send($request);
        } catch(RequestException $e) {
            throw new CommunicationException("RequestException", CommunicationException::HTTP_ERROR, $e);
        }
    }

    /**
     * @param array $array
     * @return array
     */
    protected function attachAuth($array)
    {
        return array_merge(['username' => $this->account->getUserName(), 'password' => $this->account->getPassword()], $array);
    }
}