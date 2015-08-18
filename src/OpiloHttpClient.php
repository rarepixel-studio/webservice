<?php

namespace OpiloClient;

use GuzzleHttp\Client;
use OpiloClient\Configs\OpiloAccount;
use OpiloClient\Configs\OpiloConnectionConfig;

class OpiloHttpClient
{
    /**
     * @var OpiloAccount
     */
    protected $account;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(OpiloConnectionConfig $config, OpiloAccount $account)
    {
        $this->client = $config->getHttpClient();
        $this->account = $account;
    }

    public function sendSMS()
    {

    }

    public function receiveSMS()
    {

    }

    public function getSMSStatus()
    {

    }

    public function getCredit()
    {

    }
}