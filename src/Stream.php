<?php

namespace StasPiv\DgtDrvPhp;

use SplObserver;
use StasPiv\DgtDrvPhp\StreamReader\DgtBoardStreamReader;

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

    /** @var string */
    private $port;

    /** @var array */
    private $pipes;

    /**
     * DgtBoardStream constructor.
     */
    public function __construct()
    {
        exec('ls /dev/ | grep ttyACM', $output);

        if (!isset($output) || count($output) !== 1) {
            throw new \RuntimeException('Unable to find DGT board or too many devices connected');
        }

        $this->port = '/dev/' . $output[0];

        $this->handle = proc_open('cu -l ' . $this->port . ' -s baud-rate-speed', array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("file", 'tty-error.txt' , "w")   // stderr is a file to write to
        ), $this->pipes);

        if (!$this->handle) {
            throw new \RuntimeException('Unable to open port ' . $this->port);
        }
    }

    public function start(callable $callable = null)
    {
        $errorContent = file_get_contents('tty-error.txt');
        if (!empty($errorContent)) {
            throw new \RuntimeException($errorContent);
        }

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
        fwrite($this->pipes[0], chr($number), 1);
    }

    private function read(): int
    {
        return ord(fread($this->pipes[1], 1));
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
        $this->write(DgtBoardStreamReader::SEND_RESET);

        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }

        proc_close($this->handle);
    }
}