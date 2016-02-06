<?php

namespace OpiloClient\V2\Bin;

use OpiloClient\Request\IncomingSMS;
use OpiloClient\Response\CheckStatusResponse;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;
use Psr\Http\Message\ResponseInterface as Response6Interface;
use GuzzleHttp\Message\ResponseInterface as Response5Interface;


class Parser
{
    /**
     * @param Response5Interface|Response6Interface $response
     *
     * @return string
     *
     * @throws CommunicationException
     */
    public static function getRawResponseBody($response)
    {
        $statusCode = $response->getStatusCode();
        $rawResponse = $response->getBody()->getContents();

        if ($statusCode != 200) {
            throw CommunicationException::createFromHTTPResponse($statusCode, $rawResponse);
        }

        return $rawResponse;
    }

    /**
     * @param Response5Interface|Response6Interface $response
     *
     * @return Status[]
     *
     * @throws CommunicationException
     *
     * @return Credit
     */
    public static function prepareCredit($response)
    {
        $rawResponse = static::getRawResponseBody($response);
        $array = json_decode($rawResponse, true);
        if (!is_array($array) || !array_key_exists('sms_page_count', $array)) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        return new Credit($array['sms_page_count']);
    }

    /**
     * @param Response5Interface|Response6Interface $response
     *
     * @return Inbox
     *
     * @throws CommunicationException
     */
    public static function prepareIncomingSMS($response)
    {
        $rawResponse = static::getRawResponseBody($response);

        $array = json_decode($rawResponse, true);
        if (is_null($array) || !is_array($array) || !array_key_exists('messages', $array) || !is_array($array['messages'])) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        $array = $array['messages'];

        $prepared = [];

        foreach ($array as $id => $item) {
            if (!is_array($item) || !array_key_exists('from', $item) || !array_key_exists('to', $item) ||
                !array_key_exists('text', $item) || !array_key_exists('received_at', $item)) {
                throw new CommunicationException("Unprocessable Response item: $rawResponse",
                    CommunicationException::UNPROCESSABLE_RESPONSE_ITEM);
            }
            $prepared[] = new IncomingSMS($id, $item['from'], $item['to'], $item['text'],
                \DateTime::createFromFormat('Y-m-d H:i:s', $item['received_at']));
        }

        return new Inbox($prepared);
    }

    /**
     * @param string $rawResponse
     *
     * @return Status[]
     *
     * @throws CommunicationException
     */
    protected static function makeStatusArray($rawResponse)
    {
        $array = json_decode($rawResponse, true);
        if (is_null($array) || !is_array($array) || !array_key_exists('status_array', $array) || !is_array($array['status_array'])) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        $array = $array['status_array'];

        $prepared = [];

        foreach ($array as $item) {
            if (!is_numeric($item)) {
                throw new CommunicationException("Unprocessable Response item: $rawResponse",
                    CommunicationException::UNPROCESSABLE_RESPONSE_ITEM);
            }
            $prepared[] = new Status((int) $item);
        }

        return new CheckStatusResponse($prepared);
    }

    /**
     * @param Response5Interface|Response6Interface $response
     *
     * @throws CommunicationException
     *
     * @return Status[]
     */
    public static function prepareStatusArray($response)
    {
        $rawResponse = static::getRawResponseBody($response);

        return static::makeStatusArray($rawResponse);
    }

    /**
     * @param Response5Interface|Response6Interface $response
     *
     * @return SendSMSResponse[]
     *
     * @throws CommunicationException
     */
    public static function prepareSendResponse($response)
    {
        $rawResponse = static::getRawResponseBody($response);

        return static::makeSendResponseArray($rawResponse);
    }

    /**
     * @param string $rawResponse
     *
     * @return SendSMSResponse[]
     *
     * @throws CommunicationException
     */
    protected static function makeSendResponseArray($rawResponse)
    {
        $array = json_decode($rawResponse, true);
        if (is_null($array) || !is_array($array) || !array_key_exists('messages', $array) || !is_array($array['messages'])) {
            throw new CommunicationException("Unprocessable Response: $rawResponse",
                CommunicationException::UNPROCESSABLE_RESPONSE);
        }
        $array = $array['messages'];
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
