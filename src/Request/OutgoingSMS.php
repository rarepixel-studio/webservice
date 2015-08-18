<?php

namespace OpiloClient\Request;

class OutgoingSMS extends SMS
{
    /**
     * @var string
     */
    protected $userDefinedId;

    /**
     * SMS constructor.
     * @param string $from
     * @param string $to
     * @param string $text
     * @param string|null $userDefinedId
     */
    public function __construct($from, $to, $text, $userDefinedId = null)
    {
        parent::__construct($from, $to, $text);
        $this->userDefinedId = $userDefinedId;
    }

    /**
     * @return string
     */
    public function getUserDefinedId()
    {
        return $this->userDefinedId;
    }
}