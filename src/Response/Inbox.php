<?php

namespace OpiloClient\Response;


use OpiloClient\Request\IncomingSMS;

class Inbox
{
    const PAGE_LIMIT = 90;

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

    /**
     * @return \OpiloClient\Request\IncomingSMS[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

}