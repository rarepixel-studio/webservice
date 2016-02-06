<?php

namespace OpiloClient\V1;

use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;
use OpiloClient\V1\Bin\Out5;
use OpiloClient\V1\Bin\Parser;

class HttpClient5 extends HttpClient
{
    /**
     * @param string $from
     * @param string|array $to
     * @param string $text
     *
     * @return SendError[]|SendSMSResponse[]|SMSId[]
     *
     * @throws CommunicationException
     */
    public function sendSMS($from, $to, $text)
    {
        if (!is_array($to)) {
            $to = [$to];
        }
        $to = implode(',', $to);

        $request  = $this->client->createRequest('GET', 'httpsend', [
                'query' => Out5::attachAuth($this->account, [
                    'from' => $from,
                    'to'   => $to,
                    'text' => $text,]),
            ]
        );
        $response = Out5::send($this->client, $request);

        return Parser::prepareSendResponse($response);
    }

    /**
     * @param int $fromId
     * @param string|null $fromDate
     * @param int $read
     * @param string|null $number
     * @param int $count
     *
     * @return Inbox
     *
     * @throws CommunicationException
     */
    public function checkInbox($fromId = 0, $fromDate = null, $read = 0, $number = null, $count = Inbox::PAGE_LIMIT)
    {
        $query = [];
        if ($fromId) {
            $query['from_id'] = $fromId;
        }
        if ($fromDate) {
            $query['from_date'] = $fromDate;
        }
        if ($read) {
            $query['read'] = 1;
        }
        if ($number) {
            $query['number'] = $number;
        }
        if ($count) {
            $query[$count] = $count;
        }
        $request  = $this->client->createRequest('GET', 'getAllMessages', [
            'query' => Out5::attachAuth($this->account, $query),
        ]);
        $response = Out5::send($this->client, $request);

        return Parser::prepareInbox($response);
    }

    /**
     * @param int $from offset from
     * @param int $count
     *
     * @deprecated
     *
     * @return Inbox
     */
    public function receive($from = 0, $count = Inbox::PAGE_LIMIT)
    {
        $query = [];
        if ($from) {
            $query['from'] = $from;
        }
        if ($count) {
            $query['count'] = $count;
        }
        $request  = $this->client->createRequest('GET', 'recieve', [
            'query' => Out5::attachAuth($this->account, $query),
        ]);
        $response = Out5::send($this->client, $request);

        return Parser::prepareInbox($response);
    }

    /**
     * @param int|int[] $opiloIds
     *
     * @return Status[]
     */
    public function checkStatus($opiloIds)
    {
        if (!is_array($opiloIds)) {
            $opiloIds = [$opiloIds];
        }
        $request  = $this->client->createRequest('GET', 'getStatus', [
            'query' => Out5::attachAuth($this->account, [
                'ids' => $opiloIds,
            ]),]);
        $response = Out5::send($this->client, $request);

        return Parser::prepareStatusArray($opiloIds, $response);
    }

    /**
     * @return Credit
     *
     * @throws CommunicationException
     */
    public function getCredit()
    {
        $request  = $this->client->createRequest('GET', 'getCredit', [
            'query' => Out5::attachAuth($this->account, []),
        ]);
        $response = Out5::send($this->client, $request);

        return Parser::prepareCredit($response);
    }
}
