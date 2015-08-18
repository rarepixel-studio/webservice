<?php

namespace OpiloClient\Configs;

use GuzzleHttp\Client;

class OpiloConnectionConfig
{
    /**
     * @var string Server Base URI, e.g. https://bpanel.opilo.com
     */
    protected $serverBaseUrl;

    /**
     * @var string API version.
     */
    protected $apiVersion;

    /**
     * OpiloConnectionConfig constructor
     * @param string $serverBaseUrl
     * @param string $apiVersion
     */
    public function __construct($serverBaseUrl, $apiVersion = '2')
    {
        $this->serverBaseUrl = $serverBaseUrl;
        $this->apiVersion = $apiVersion;
    }

    public function getHttpClient()
    {
        return new Client(['base_url' => $this->serverBaseUrl . '/ws/api/v' . $this->apiVersion . '/']);
    }
}