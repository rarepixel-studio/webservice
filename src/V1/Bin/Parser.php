<?php

namespace OpiloClient\V1\Bin;

use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Request\IncomingSMS;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;

class Parser
{
    const VALIDATION_FAILED = 1;
    const AUTHENTICATION_FAILED = 2;
    const PANEL_AUTHORIZATION_FAILED = 3;
    const WEB_SERVICE_AUTHORIZATION_FAILED = 4;
    const INVALID_FROM = 5;
    const INVALID_TO = 6;
    const INSUFFICIENT_CREDIT = 7;
    const INTERNAL_SERVER_ERROR = 8;



    const STATUS_QUEUED = 0;
    const STATUS_DELIVERED_TO_DESTINATION = 1;
    const STATUS_FAILED_TO_DELIVER_TO_DESTINATION = 2;
    const STATUS_DELIVERED_TO_COMMUNICATION_CO = 8;
    const STATUS_FAILED_TO_DELIVER_TO_COMMUNICATION_CO = 16;
    const STATUS_BLOCKED = 27;

    private static $statusMap = [
        self::STATUS_QUEUED => Status::QUEUED,
        self::STATUS_DELIVERED_TO_DESTINATION => Status::DELIVERED_TO_DESTINATION,
        self::STATUS_FAILED_TO_DELIVER_TO_DESTINATION => Status::FAILED_TO_DELIVER_TO_DESTINATION,
        self::STATUS_DELIVERED_TO_COMMUNICATION_CO => Status::DELIVERED_TO_COMMUNICATION_CO,
        self::STATUS_FAILED_TO_DELIVER_TO_COMMUNICATION_CO=> Status::REJECTED_BY_COMMUNICATION_CO_AND_REFUNDED,
        self::STATUS_BLOCKED => Status::REJECTED_BY_OPERATOR_AND_REFUNDED,
    ];

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
            throw new CommunicationException("StatusCode: $statusCode, Contents: $rawResponse", CommunicationException::GENERAL_HTTP_ERROR);
        }
        static::checkForErrors($rawResponse);
        return $rawResponse;
    }

    private static function checkForErrors($rawResponse)
    {
        if(is_numeric($rawResponse)) {
            switch($rawResponse) {
                case static::VALIDATION_FAILED:
                    throw new CommunicationException('Input Validation Failed', CommunicationException::INVALID_INPUT);
                case static::AUTHENTICATION_FAILED:
                    throw CommunicationException::createFromHTTPResponse('401', '');
                case static::PANEL_AUTHORIZATION_FAILED:
                    throw new CommunicationException('Forbidden [Panel is disabled]', CommunicationException::FORBIDDEN);
                case static::WEB_SERVICE_AUTHORIZATION_FAILED:
                    throw CommunicationException::createFromHTTPResponse('403', '');
                case static::INVALID_FROM:
                    throw new CommunicationException('Invalid From', CommunicationException::INVALID_INPUT);
                case static::INVALID_TO:
                    throw new CommunicationException('Invalid To', CommunicationException::INVALID_INPUT);
                case static::INTERNAL_SERVER_ERROR:
                    throw new CommunicationException('Internal Server Error', CommunicationException::GENERAL_HTTP_ERROR);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @return Credit
     * @throws CommunicationException
     */
    public static function prepareCredit(ResponseInterface $response)
    {
        return new Credit(static::getRawResponseBody($response));
    }

    /**
     * @param ResponseInterface $response
     * @return SendSMSResponse[]|SMSId[]|SendError[]
     * @throws CommunicationException
     */
    public static function prepareSendResponse(ResponseInterface $response)
    {
        $body = static::getRawResponseBody($response);
        $decoded = json_decode($body, true);
        if(is_numeric($body)) {
            $decoded = [$decoded];
        }
        $output = [];
        foreach ($decoded as $id) {
            if($id < 10) {
                $output[] = new SendError($id);
            }
            else {
                $output[] = new SMSId($id);
            }
        }
        return $output;
    }

    /**
     * @param int[] $opiloIds
     * @param $response
     * @return status[]
     * @throws CommunicationException
     */
    public static function prepareStatusArray($opiloIds, ResponseInterface $response)
    {
        $body = static::getRawResponseBody($response);
        $decoded = json_decode($body, true);

        if(! is_array($decoded)) {
            throw new CommunicationException("Unprocessable Response: $body", CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        $output = [];

        foreach($opiloIds as $opiloId) {
            $output[$opiloId] = new Status(Status::NOT_FOUND);
        }

        foreach ($decoded as $sms) {
            if(!is_array($sms) || !array_key_exists('id', $sms) || !array_key_exists('status', $sms) || !array_key_exists($sms['status'], static::$statusMap)) {
               throw new CommunicationException("Unprocessable Response item: $body", CommunicationException::UNPROCESSABLE_RESPONSE_ITEM);
            }
            $output[$sms['id']] = new Status(static::$statusMap[$sms['status']]);
        }

        return array_values($output);
    }

    /**
     * @param ResponseInterface $response
     * @return Inbox
     * @throws CommunicationException
     */
    public static function prepareInbox(ResponseInterface $response)
    {
        $body = static::getRawResponseBody($response);

        $decoded = json_decode($body, true);
        if(! is_array($decoded)) {
            throw new CommunicationException("Unprocessable Response: $body", CommunicationException::UNPROCESSABLE_RESPONSE);
        }

        $output = [];
        foreach ($decoded as $sms) {
            if(!is_array($sms) || ! array_key_exists('id', $sms) || ! array_key_exists('from', $sms) || ! array_key_exists('to', $sms) || ! array_key_exists('date', $sms)) {
                throw new CommunicationException("Unprocessable Response item: $body", CommunicationException::UNPROCESSABLE_RESPONSE_ITEM);
            }
            $output[] = new IncomingSMS($sms['id'], $sms['from'], $sms['to'], $sms['text'], \DateTime::createFromFormat('Y-m-d H:i:s', $sms['date']));
        }

        return new Inbox($output);
    }
}