<?php

namespace OpiloClient\Response;


use OpiloClient\Request\IncomingSMS;

class Inbox
{
    const PAGE_LIMIT = 90;

    const INBOX_READ = 'read';
    const INBOX_NOT_READ = 'not_read';
    const INBOX_ALL = 'all';

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