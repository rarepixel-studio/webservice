<?php

namespace OpiloClient\Response;

class Status
{
    /**
     * The Message is in Opilo server queues, waiting to be sent.
     */
    const QUEUED = 0;

    /**
     * The message is sent to operator. It has operator-side-id. The operator is about to send the message to the communication co.
     */
    const DELIVERED_TO_OPERATOR = 1;

    /**
     * The message is sent to communication co. by the operator. It has operator-side-id.
     */
    const DELIVERED_TO_COMMUNICATION_CO = 2;

    /**
     * The message is delivered in the target destination.
     */
    const DELIVERED_TO_DESTINATION = 3;

    /**
     * The message was dropped while the communication co. was trying to deliver it to the target destination.
     * The operator is not going to do any refund. The communication co. is not going to retry to send the message anymore, so the status is final.
     */
    const FAILED_TO_DELIVER_TO_DESTINATION = 4;

    /**
     * The message is dropped by Opilo while trying to send, and is refunded.
     */
    const DROPPED_AND_REFUNDED = -1;

    /**
     * The message was rejected while Opilo was trying to send it to operator. Operator did not charged the account for this message.
     */
    const REJECTED_BY_OPERATOR_AND_REFUNDED = -2;

    /**
     * The message was rejected while operator was trying to send it to communication co. and the operator has refunded it.
     */
    const REJECTED_BY_COMMUNICATION_CO_AND_REFUNDED = -3;

    /**
     * The message was dropped while the communication co. was trying to deliver it to the target destination and operator has refunded it.
     */
    const REJECTED_BY_DESTINATION_AND_REFUNDED = -4;

    /**
     * No message with this id is found.
     */
    const NOT_FOUND = -5;
    /**
     * @var int 0,1,2,3,4,-1,-2,-3 see constants defined above
     */
    protected $code;

    /**
     * Status constructor.
     *
     * @param int $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }
}
