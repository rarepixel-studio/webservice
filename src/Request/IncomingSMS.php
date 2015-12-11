<?php

namespace OpiloClient\Request;

use DateTime;

class IncomingSMS extends SMS
{
    /**
     * @var DateTime
     */
    protected $receivedAt;

    /**
     * @var int
     */
    protected $opiloId;

    /**
     * IncomingSMS constructor.
     *
     * @param int      $opiloId
     * @param string   $from
     * @param string   $to
     * @param string   $text
     * @param DateTime $receivedAt
     */
    public function __construct($opiloId, $from, $to, $text, DateTime $receivedAt)
    {
        parent::__construct($from, $to, $text);
        $this->receivedAt = $receivedAt;
        $this->opiloId = $opiloId;
    }

    /**
     * @return DateTime
     */
    public function getReceivedAt()
    {
        return $this->receivedAt;
    }

    /**
     * @return int
     */
    public function getOpiloId()
    {
        return $this->opiloId;
    }
}
