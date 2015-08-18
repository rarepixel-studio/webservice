<?php

namespace OpiloClient\Response;

use Exception;

class CommunicationException extends Exception
{
    const HTTP_ERROR = 1;
    const UNPROCESSABLE_RESPONSE = 2;
    const UNPROCESSABLE_RESPONSE_ITEM = 3;

}