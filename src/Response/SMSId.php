<?php

namespace OpiloClient\Response;

class SMSId extends SendSMSResponse
{
    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var bool
     */
    protected $duplicate;

    /**
     * OpiloSMSId constructor.
     *
     * @param string|null $id
     * @param bool $duplicate
     */
    public function __construct($id, $duplicate = false)
    {
        $this->id = $id;
        $this->duplicate = $duplicate;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isDuplicated()
    {
        return $this->duplicate;
    }
}
