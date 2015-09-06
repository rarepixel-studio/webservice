<?php

namespace OpiloClient\Response;


use OpiloClient\Request\IncomingSMS;

class Inbox
{

    /**
     * @var IncomingSMS[]
     */
    protected $messages;

    /**
     * Inbox constructor.
     * @param IncomingSMS[] $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

}