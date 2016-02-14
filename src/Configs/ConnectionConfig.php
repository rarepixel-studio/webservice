<?php

namespace OpiloClient\Configs;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

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
