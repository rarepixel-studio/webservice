<?php

namespace OpiloClient\Response;

class SMSId extends SendSMSResponse
{
    /**
     * @var string|null
     */
    protected $id;

    /**
     * OpiloSMSId constructor.
     *
     * @param string|null $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }
}
