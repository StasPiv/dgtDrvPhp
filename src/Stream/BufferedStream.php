<?php

namespace StasPiv\DgtDrvPhp\Stream;

use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\NewMessageReceived;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\SendMessageRequested;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\StartListeningWebsocket;
use StasPiv\DgtDrvPhp\StreamInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class BufferedStream
 *
 * @package StasPiv\DgtDrvPhp\Stream
 */
class BufferedStream implements StreamInterface
{
    use SplSubjectTrait;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * BufferedStream constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function start(callable $callable = null)
    {
        $this->dispatcher->dispatch(new StartListeningWebsocket());
    }

    public function onNewMessageReceived(NewMessageReceived $event)
    {
        $this->readFromWebsocket($event->getMessage());
    }

    public function write(int $number)
    {
        $this->dispatcher->dispatch(new SendMessageRequested('SEND TO DGT: ' . chr($number)));
    }

    /**
     * @param string $buffer
     */
    private function readFromWebsocket(string $buffer)
    {
        for ($i = 0; $i < strlen($buffer); $i++) {
            foreach ($this->readers as $reader) {
                $boardMessage = ord(substr($buffer, $i, 1));
                $this->setBoardMessage($boardMessage);
                $reader->update($this);
            }
        }
    }
}
