<?php

namespace OpiloClient\V1\Bin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use OpiloClient\Configs\Account;
use OpiloClient\Response\CommunicationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Out6
{
    /**
     * @param Client $client
     * @param RequestInterface $request
     *
     * @param Account $account
     * @param array $params
     * @return ResponseInterface
     * @throws CommunicationException
     */
    public static function send(Client $client, RequestInterface $request, Account $account, $params = [])
    {
        try {
            return $client->send($request, [
                'query' => self::attachAuth($account, $params),
            ]);
        } catch (RequestException $e) {
            throw new CommunicationException('RequestException', CommunicationException::GENERAL_HTTP_ERROR, $e);
        }
    }

    /**
     * @param Account $account
     * @param array $array
     *
     * @return array
     */
    private static function attachAuth(Account $account, $array)
    {
        return array_merge(['username' => $account->getUserName(), 'password' => $account->getPassword()], $array);
    }
}
