<?php

namespace OpiloClient\Response;

class SendError extends SendSMSResponse
{
    const ERROR_OUT_OF_CREDIT = 1;

    const ERROR_RESOURCE_NOT_FOUND = 2;

    const ERROR_GENERAL = 3;

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