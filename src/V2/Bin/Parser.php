<?php

namespace OpiloClient\V2\Bin;

use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Request\IncomingSMS;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;

class Parser
{
    /**
     * @param ResponseInterface $response
     * @return string
     * @throws CommunicationException
     */
    public static function getRawResponseBody(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $rawResponse = $response->getBody()->getContents();

        if ($statusCode != 200) {
            throw CommunicationException::createFromHTTPResponse($statusCode, $rawResponse);
        }
        return $rawResponse;
    }

    /**
     * @param ResponseInterface $response
     * @return Status[]
     * @throws CommunicationException
     * @return Credit
     */
    public static function prepareCredit(ResponseInterface $response)
    {
        $rawResponse = static::getRawResponseBody($response);
        $array = json_decode($rawResponse, true);
        if(! is_array($array) || ! array_key_exists('sms_page_count', $array)) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }
        return new Credit($array['sms_page_count']);
    }

    /**
     * @param ResponseInterface $response
     * @throws CommunicationException
     * @return IncomingSMS[]
     */
    public static function prepareIncomingSMS(ResponseInterface $response)
    {
        $rawResponse = static::getRawResponseBody($response);

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
     * @param string $rawResponse
     * @return Status[]
     * @throws CommunicationException
     */
    protected static function makeStatusArray($rawResponse)
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
     * @throws CommunicationException
     * @return Status[]
     */
    public static function prepareStatusArray(ResponseInterface $response)
    {
        $rawResponse = static::getRawResponseBody($response);
        return static::makeStatusArray($rawResponse);
    }

    /**
     * @param ResponseInterface $response
     * @return SendSMSResponse[]
     * @throws CommunicationException
     */
    public static function prepareSendResponse(ResponseInterface $response)
    {
        $rawResponse = static::getRawResponseBody($response);

        return static::makeSendResponseArray($rawResponse);
    }

    /**
     * @param string $rawResponse
     * @return SendSMSResponse[]
     * @throws CommunicationException
     */
    protected static function makeSendResponseArray($rawResponse)
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
}