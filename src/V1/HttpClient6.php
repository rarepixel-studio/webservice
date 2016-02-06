<?php

namespace OpiloClient\V1;

use GuzzleHttp\Psr7\Request;
use OpiloClient\Response\CommunicationException;
use OpiloClient\Response\Credit;
use OpiloClient\Response\Inbox;
use OpiloClient\Response\SendError;
use OpiloClient\Response\SendSMSResponse;
use OpiloClient\Response\SMSId;
use OpiloClient\Response\Status;
use OpiloClient\V1\Bin\Out6;
use OpiloClient\V1\Bin\Parser;

class HttpClient6 extends HttpClient
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

        $request  = new Request('GET', 'httpsend');
        $response = Out6::send($this->client, $request, $this->account, [
            'from' => $from,
            'to'   => $to,
            'text' => $text,
        ]);

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
        $request  = new Request('GET', 'getAllMessages');
        $response = Out6::send($this->client, $request, $this->account, $query);

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
        $request  = new Request('GET', 'recieve');
        $response = Out6::send($this->client, $request, $this->account, $query);

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
        $request  = new Request('GET', 'getStatus');
        $response = Out6::send($this->client, $request, $this->account, ['ids' => $opiloIds]);

        return Parser::prepareStatusArray($opiloIds, $response);
    }

    /**
     * @return Credit
     *
     * @throws CommunicationException
     */
    public function getCredit()
    {
        $request  = new Request('GET', 'getCredit');
        $response = Out6::send($this->client, $request, $this->account);

        return Parser::prepareCredit($response);
    }
}
