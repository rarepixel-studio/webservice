<?php

namespace OpiloClient\Response;

class DuplicateSmsError extends SendError
{
    /**
     * @var int
     */
    private $id;

    public function __construct($id)
    {
        parent::__construct(SendError::ERROR_DUPLICATE_SMS);
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
