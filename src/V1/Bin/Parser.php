<?php

namespace OpiloClient\V1\Bin;

use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Response\CommunicationException;

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
                    throw CommunicationException::createFromHTTPResponse('422', '');
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
}