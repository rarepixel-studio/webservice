<?php

namespace OpiloClient\Response;

class CheckStatusResponse
{
    /**
     * @var Status[]
     */
    protected $statusArray;

    /**
     * CheckStatusResponse constructor.
     * @param Status[] $statusArray
     */
    public function __construct(array $statusArray)
    {
        $this->statusArray = $statusArray;
    }

    /**
     * @return Status[]
     */
    public function getStatusArray()
    {
        return $this->statusArray;
    }
}