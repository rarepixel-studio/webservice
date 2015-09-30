<?php

namespace OpiloClient\Request;

use DateTime;

class OutgoingSMS extends SMS
{
    /**
     * @var string
     */
    protected $userDefinedId;

    /**
     * @var DateTime|null
     */
    protected $sendAt;

    /**
     * SMS constructor.
     * @param string $from
     * @param string $to
     * @param string $text
     * @param string|null $userDefinedId
     * @param null|DateTime $sendAt
     */
    public function __construct($from, $to, $text, $userDefinedId = null, $sendAt = null)
    {
        parent::__construct($from, $to, $text);
        $this->userDefinedId = $userDefinedId;
        $this->sendAt = $sendAt;
    }

    /**
     * @return string
     */
    public function getUserDefinedId()
    {
        return $this->userDefinedId;
    }

    /**
     * @return DateTime|null
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    public function formatSendAt()
    {
        return $this->sendAt ? $this->sendAt->format('Y-m-d H:i:s') : null;
    }
}