<?php

namespace OpiloClient\Response;

class Credit
{
    /**
     * @var int
     */
    private $smsPageCount;

    /**
     * Credit constructor.
     *
     * @param int $smsPageCount
     */
    public function __construct($smsPageCount)
    {
        $this->smsPageCount = (int) $smsPageCount;
    }

    /**
     * @return int
     */
    public function getSmsPageCount()
    {
        return $this->smsPageCount;
    }
}
