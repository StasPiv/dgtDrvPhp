<?php

namespace StasPiv\DgtDrvPhp\Stream\BufferedStream;

use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\NewMessageReceived;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\SendMessageRequested;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\StartListeningWebsocket;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebSocket\Client;
use WebSocket\ConnectionException;

class WebsocketListener
{
    /** @var Client */
    private $wsClient;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * WebsocketListener constructor.
     *
     * @param Client                   $wsClient
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Client $wsClient, EventDispatcherInterface $dispatcher)
    {
        $this->wsClient = $wsClient;
        $this->dispatcher = $dispatcher;
    }

    public function onStartListening(StartListeningWebsocket $event)
    {
        $this->listen();
    }

    public function onSendMessageRequested(SendMessageRequested $event)
    {
        $this->wsClient->send($event->getMessage());
    }

    private function listen()
    {
        while (true) {
            try {
                $this->dispatcher->dispatch(new NewMessageReceived($this->wsClient->receive()));
            } catch (ConnectionException $exception) {
                continue;
            }
        }
    }
}
