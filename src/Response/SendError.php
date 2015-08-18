<?php

namespace OpiloClient\Response;

class SendError extends SendSMSResponse
{
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