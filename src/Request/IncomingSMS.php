<?php

namespace OpiloClient\Request;

use DateTime;

class IncomingSMS extends SMS
{
    /**
     * @var DateTime
     */
    protected $timestamp;

    /**
     * @var int
     */
    protected $opiloId;

    /**
     * IncomingSMS constructor.
     * @param int $opiloId
     * @param string $from
     * @param string $to
     * @param string $text
     * @param DateTime $timestamp
     */
    public function __construct($opiloId, $from, $to, $text, DateTime $timestamp)
    {
        parent::__construct($from, $to, $text);
        $this->timestamp = $timestamp;
        $this->opiloId = $opiloId;
    }

    /**
     * @return DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getOpiloId()
    {
        return $this->opiloId;
    }
}