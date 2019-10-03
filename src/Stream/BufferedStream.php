<?php

namespace StasPiv\DgtDrvPhp\Stream;

use StasPiv\DgtDrvPhp\StreamInterface;
use WebSocket\Client;

/**
 * Class BufferedStream
 *
 * @package StasPiv\DgtDrvPhp\Stream
 */
class BufferedStream implements StreamInterface
{
    use SplSubjectTrait;

    /** @var Client */
    private $wsClient;

    /**
     * BufferedStream constructor.
     *
     * @param Client $wsClient
     */
    public function __construct(Client $wsClient)
    {
        $this->wsClient = $wsClient;
    }

    public function start(callable $callable = null)
    {
        while (true) {
            $receivedMessage = $this->wsClient->receive();
            $this->readFromWebsocket($receivedMessage);
        }
    }

    public function write(int $number)
    {
        $this->wsClient->send(chr($number));
    }

    /**
     * @param string $buffer
     */
    private function readFromWebsocket(string $buffer)
    {
        for ($i = 0; $i < strlen($buffer); $i++) {
            foreach ($this->readers as $reader) {
                $boardMessage = ord($buffer{$i});

                if (dechex($boardMessage) === 'c2') { // first symbol
                    continue;
                }

                $this->setBoardMessage($boardMessage);
                $reader->update($this);
            }
        }
    }
}
