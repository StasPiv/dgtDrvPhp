<?php

namespace StasPiv\DgtDrvPhp\Stream;

use StasPiv\DgtDrvPhp\Exception\OptionsRequiredException;
use StasPiv\DgtDrvPhp\Exception\UnknownStreamTypeException;
use StasPiv\DgtDrvPhp\Stream;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\NewMessageReceived;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\SendMessageRequested;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\Event\StartListeningWebsocket;
use StasPiv\DgtDrvPhp\Stream\BufferedStream\WebsocketListener;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StreamFactory
{
    public static function create(string  $type, array $options = [])
    {
        switch ($type) {
            case StreamType::BUFFERED:
                if (!isset($options['ws']) || !isset($options['dispatcher'])) {
                    throw new OptionsRequiredException('Required option: ws, dispatcher');
                }

                /** @var EventDispatcher $dispatcher */
                $dispatcher = $options['dispatcher'];

                $bufferedStream = new BufferedStream($dispatcher);
                $websocketListener = new WebsocketListener($options['ws'], $dispatcher);

                $dispatcher->addListener(NewMessageReceived::class, [$bufferedStream, 'onNewMessageReceived']);
                $dispatcher->addListener(StartListeningWebsocket::class, [$websocketListener, 'onStartListening']);
                $dispatcher->addListener(SendMessageRequested::class, [$websocketListener, 'onSendMessageRequested']);

                return $bufferedStream;
            case StreamType::CU:
                return new Stream(isset($options['connectionType']) ? $options['connectionType'] : ConnectionType::BLUETOOTH);
            default:
                throw new UnknownStreamTypeException();
        }
    }
}
