<?php

namespace OpiloClient\V2\Bin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use OpiloClient\Configs\Account;
use OpiloClient\Request\OutgoingSMS;
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
                'to' => $message->getTo(),
                'id' => $message->getUserDefinedId(),
            ];

            if ($array['defaults']['from'] != $message->getFrom()) {
                $msg['from'] = $message->getFrom();
            }

            if ($array['defaults']['text'] != $message->getText()) {
                $msg['text'] = $message->getText();
            }

            if ($message->getSendAt() &&
                (!array_key_exists('send_at', $array['defaults']) ||
                    $array['defaults']['send_at'] != $message->formatSendAt())) {
                $msg['send_at'] = $message->formatSendAt();
            }

            $array['messages'][] = $msg;
        }

        return $array;
    }
}
