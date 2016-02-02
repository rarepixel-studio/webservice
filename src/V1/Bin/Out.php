<?php

namespace OpiloClient\V1\Bin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use OpiloClient\Configs\Account;
use OpiloClient\Response\CommunicationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Out
{
    /**
     * @param Client           $client
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws CommunicationException
     */
    public static function send(Client $client, RequestInterface $request)
    {
        try {
            return $client->send($request);
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
