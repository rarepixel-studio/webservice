<?php

namespace OpiloClient\Configs;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ConnectionConfig
{
    const DEFAULT_SERVER_BASE_URL = 'http://bpanel.opilo.com';

    const VERSION_1 = '1';
    const VERSION_2 = '2';

    /**
     * @var string Server Base URI
     */
    protected $serverBaseUrl;

    /**
     * OpiloConnectionConfig constructor.
     *
     * @param string|null $serverBaseUrl
     */
    public function __construct($serverBaseUrl = null)
    {
        $this->serverBaseUrl = $serverBaseUrl ?: static::DEFAULT_SERVER_BASE_URL;
    }

    public function getHttpClient($apiVersion = self::VERSION_2)
    {
        $version = ClientInterface::VERSION;
        $version = $version[0];
        if ($version === '5') {
            return new Client([
                'base_url' => $this->serverBaseUrl . $this->getVersionSegment($apiVersion),
                'defaults' => ['exceptions' => false],
            ]);
        } elseif ($version === '6') {
            return new Client([
                'base_uri'   => $this->serverBaseUrl . $this->getVersionSegment($apiVersion),
                'exceptions' => false,
            ]);
        } else {
            throw new \RuntimeException('Unknown Guzzle version: ' . $version);
        }
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
