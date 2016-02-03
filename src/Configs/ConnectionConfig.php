<?php

namespace OpiloClient\Configs;

use GuzzleHttp\Client;

class ConnectionConfig
{
    const VERSION_1 = '1';
    const VERSION_2 = '2';

    /**
     * @var string Server Base URI, e.g. https://bpanel.opilo.com
     */
    protected $serverBaseUrl;

    /**
     * OpiloConnectionConfig constructor.
     *
     * @param string $serverBaseUrl
     */
    public function __construct($serverBaseUrl)
    {
        $this->serverBaseUrl = $serverBaseUrl;
    }

    public function getHttpClient($apiVersion = self::VERSION_2)
    {
        return new Client([
            'base_uri' => $this->serverBaseUrl . $this->getVersionSegment($apiVersion),
            'exceptions' => false
        ]);
    }

    /**
     * @param $apiVersion
     *
     * @return string
     */
    protected function getVersionSegment($apiVersion)
    {
        if ($apiVersion == self::VERSION_1) {
            return '/WS/';
        }

        return ('/ws/api/v' . $apiVersion . '/');
    }
}
