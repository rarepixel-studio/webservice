<?php

namespace OpiloClient\Response;

class SendError extends SendSMSResponse
{
    const ERROR_RESOURCE_NOT_FOUND = 5;

    const ERROR_INVALID_DESTINATION = 6;

    const ERROR_OUT_OF_CREDIT = 7;

    const ERROR_GENERAL = 8;

    /**
     * @var int
     */
    protected $error;

    /**
     * SendError constructor.
     * @param int $error
     */
    public function __construct($error)
    {
        $this->error = $error;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }
}