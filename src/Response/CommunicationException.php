<?php

namespace OpiloClient\Response;

use Exception;

class CommunicationException extends Exception
{
    const GENERAL_HTTP_ERROR = 1;
    const AUTH_ERROR = 2;
    const INVALID_INPUT = 3;
    const FORBIDDEN = 4;
    const UNPROCESSABLE_RESPONSE = 10;
    const UNPROCESSABLE_RESPONSE_ITEM = 11;
    const DOWN_FOR_MAINTENANCE = 12;

    public static function createFromHTTPResponse($statusCode, $bodyContents)
    {
        switch ($statusCode) {
            case '401':
                return new static('Authentication Failed', static::AUTH_ERROR);
            case '403':
                return new static('Forbidden [Web-service is disabled]', static::FORBIDDEN);
            case '503':
                return new static('Server is down for maintenance. Retry after a few seconds', static::DOWN_FOR_MAINTENANCE);
            case '422':
                return new ValidationException($bodyContents);
            default:
                return new static("StatusCode: $statusCode, Contents: $bodyContents", static::GENERAL_HTTP_ERROR);
        }
    }
}
