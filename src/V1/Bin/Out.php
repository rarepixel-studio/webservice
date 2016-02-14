<?php

namespace OpiloClient\V1\Bin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Response\CommunicationException;
use Psr\Http\Message\RequestInterface as Request6Interface;
use Psr\Http\Message\ResponseInterface as Response6Interface;

class Out
{
    /**
     * @param Client                               $client
     * @param RequestInterface | Request6Interface $request
     * @param array                                $options
     *
     * @return ResponseInterface | Response6Interface
     *
     * @throws CommunicationException
     */
    public static function send(Client $client, $request, $options = [])
    {
        try {
            return $client->send($request, $options);
        } catch (RequestException $e) {
            throw new CommunicationException('RequestException', CommunicationException::GENERAL_HTTP_ERROR, $e);
        }
    }

    /**
     * @param Account $account
     * @param array   $array
     *
     * @return array
     */
    public static function attachAuth(Account $account, $array)
    {
        return array_merge(['username' => $account->getUserName(), 'password' => $account->getPassword()], $array);
    }
}
