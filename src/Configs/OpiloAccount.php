<?php

namespace OpiloClient\Configs;


class OpiloAccount
{
    protected $userName;
    protected $password;

    function __construct($userName, $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}