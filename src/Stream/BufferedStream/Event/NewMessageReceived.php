<?php

namespace StasPiv\DgtDrvPhp\Stream\BufferedStream\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NewMessageReceived extends Event
{
    /** @var string */
    private $message;

    /**
     * NewMessageReceived constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
