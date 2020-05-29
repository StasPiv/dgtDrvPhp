<?php

namespace StasPiv\DgtDrvPhp\Stream\BufferedStream\Event;

use Symfony\Contracts\EventDispatcher\Event;

class SendMessageRequested extends Event
{
    /** @var string */
    private $message;

    /**
     * SendMessageRequested constructor.
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
