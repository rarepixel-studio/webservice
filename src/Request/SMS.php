<?php

namespace OpiloClient\Request;


abstract class SMS
{
    /**
     * @var string Sender Line Number
     */
    protected $from;

    /**
     * @var string Reciver Mobile Number
     */
    protected $to;

    /**
     * @var string The content of SMS
     */
    protected $text;

    /**
     * SMS constructor.
     * @param string $from
     * @param string $to
     * @param string $text
     */
    public function __construct($from, $to, $text)
    {
        $this->from = $from;
        $this->to = $to;
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}