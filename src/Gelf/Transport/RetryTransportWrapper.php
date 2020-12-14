<?php

namespace Gelf\Transport;

use Gelf\MessageInterface as Message;
use Exception;

class RetryTransportWrapper extends AbstractTransport
{
    /**
     * @var AbstractTransport
     */
    protected $transport;

    /**
     * RetryTransportWrapper constructor.
     *
     * @param AbstractTransport $transport
     */
    public function __construct(AbstractTransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Sends a Message over this transport.
     *
     * @param Message $message
     *
     * @return int calls function to send message
     */
    public function send(Message $message)
    {
        try {
            return $this->transport->send($message);
        } catch (Exception $e) {
            return $this->transport->send($message);
        }
    }
}
