<?php

namespace OpiloClient\V2\Bin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Request\OutgoingSMS;
use OpiloClient\Response\CommunicationException;

class Out
{
    /**
     * @param Client $client
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws CommunicationException
     */
    public static function send(Client $client, RequestInterface $request)
    {
        try{
            return $client->send($request);
        } catch(RequestException $e) {
            throw new CommunicationException("RequestException", CommunicationException::GENERAL_HTTP_ERROR, $e);
        }
    }

    /**
     * @param Account $account
     * @param array $array
     * @return array
     */
    public static function attachAuth(Account $account, $array)
    {
        return array_merge(['username' => $account->getUserName(), 'password' => $account->getPassword()], $array);
    }

    /**
     * @param OutgoingSMS[] $messages
     * @return array
     */
    public static function SMSArrayToSendRequestBody($messages)
    {
        $array = [
            'messages' => [],
        ];

        $first = true;
        foreach ($messages as $message) {

            if($first) {
                $first = false;
                $array['defaults'] = [
                    'from' => $message->getFrom(),
                    'text' => $message->getText(),
                ];
            }

            $msg = [
                'to' => $message->getTo(),
                'id' => $message->getUserDefinedId(),
            ];

            if ($array['defaults']['from'] != $message->getFrom()) {
                $msg['from'] = $message->getFrom();
            }

            if ($array['defaults']['text'] != $message->getText()) {
                $msg['text'] = $message->getText();
            }

            $array['messages'][] = $msg;
        }

        return $array;
    }
}