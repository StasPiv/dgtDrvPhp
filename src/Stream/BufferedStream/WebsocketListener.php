<?php

namespace StasPiv\DgtDrvPhp\Stream\BufferedStream;

use ReflectionClass;
use StasPiv\ChessTrain\Event\BeforeInfiniteAnalyzeEvent;
use StasPiv\ChessTrain\Event\EngineOutputReceivedEvent;
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

    public function onEngineOutputReceived(EngineOutputReceivedEvent $event)
    {
        if (preg_match('/seldepth/', $event->getEngineOutput())) {
            $this->wsClient->send('Engine output: ' . $event->getEngineOutput());
        }
    }

    public function onBeforeInfiniteAnalyze(BeforeInfiniteAnalyzeEvent $event)
    {
        $reflectionClass = new ReflectionClass($this->wsClient);
        $reflectionProperty = $reflectionClass->getProperty('is_connected');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->wsClient, false);
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
