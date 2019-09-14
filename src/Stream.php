<?php

namespace StasPiv\DgtDrvPhp;

use SplObserver;

/**
 * Read and write to Dgt Board stream
 *
 * Class Stream
 * @package StasPiv\DgtDrvPhp
 */
class Stream implements \SplSubject
{
    /** @var bool|resource */
    private $handle;

    /** @var StreamReader[] */
    private $readers;

    /** @var int  */
    private $boardMessage;

    /**
     * DgtBoardStream constructor.
     */
    public function __construct()
    {
        exec('ls /dev/ | grep ttyACM', $output);

        if (!isset($output) || count($output) !== 1) {
            throw new \RuntimeException('Unable to find DGT board or too many devices connected');
        }

        $port = '/dev/' . $output[0];
        exec('minicom -o ' . $port);
        $this->handle = fopen($port, 'w+');

        if (!$this->handle) {
            throw new \RuntimeException('Unable to open port ' . $port);
        }
    }

    public function start(callable $callable = null)
    {
        while (true) {
            $this->setBoardMessage($this->read());
            $this->notify();
            if (isset($callable)) {
                call_user_func($callable);
            }
        }
    }

    public function write(int $number)
    {
        fwrite($this->handle, chr($number), 1);
    }

    private function read(): int
    {
        return ord(fread($this->handle, 1));
    }

    public function attach(SplObserver $observer)
    {
        $this->readers[] = $observer;
    }

    public function detach(SplObserver $observer)
    {
        if (($key = array_search($observer, $this->readers)) !== false) {
            unset($this->readers[$key]);
        }
    }

    public function notify()
    {
        foreach ($this->readers as $reader) {
            $reader->update($this);
        }
    }

    /**
     * @return int
     */
    public function getBoardMessage(): int
    {
        return $this->boardMessage;
    }

    /**
     * @param string $boardMessage
     * @return Stream
     */
    public function setBoardMessage(string $boardMessage): Stream
    {
        $this->boardMessage = $boardMessage;
        return $this;
    }

    public function __destruct()
    {
        fclose($this->handle);
    }
}