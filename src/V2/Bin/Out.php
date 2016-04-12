<?php

namespace OpiloClient\V2\Bin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use OpiloClient\Configs\Account;
use OpiloClient\Request\OutgoingSMS;
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

    /**
     * @param OutgoingSMS[] $messages
     *
     * @return array
     */
    public static function SMSArrayToSendRequestBody($messages)
    {
        $array = [
            'messages' => [],
        ];

        $first = true;
        foreach ($messages as $message) {
            if ($first) {
                $first = false;
                $array['defaults'] = [
                    'from' => $message->getFrom(),
                    'text' => $message->getText(),
                ];
                if ($message->getSendAt()) {
                    $array['defaults']['send_at'] = $message->formatSendAt();
                }
            }

            $msg = [
                'to'  => $message->getTo(),
                'uid' => $message->getUserDefinedId(),
            ];

            if ($array['defaults']['from'] != $message->getFrom()) {
                $msg['from'] = $message->getFrom();
            }

            if ($array['defaults']['text'] != $message->getText()) {
                $msg['text'] = $message->getText();
            }

            if ($message->getSendAt() &&
                (!array_key_exists('send_at', $array['defaults']) ||
                    $array['defaults']['send_at'] != $message->formatSendAt())
            ) {
                $msg['send_at'] = $message->formatSendAt();
            }

            $array['messages'][] = $msg;
        }

        return $array;
    }
}
