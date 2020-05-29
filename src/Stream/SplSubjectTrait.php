<?php

namespace StasPiv\DgtDrvPhp\Stream;

use SplObserver;
use SplSubject;
use StasPiv\DgtDrvPhp\StreamReader;

trait SplSubjectTrait
{
    /** @var StreamReader[] */
    protected $readers;

    /** @var int  */
    protected $boardMessage;

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
        /** @var SplSubject $splSubject */
        $splSubject = $this;

        foreach ($this->readers as $reader) {
            $reader->update($splSubject);
        }
    }

    /**
     * @param string $boardMessage
     *
     * @return SplSubjectTrait
     */
    public function setBoardMessage(string $boardMessage)
    {
        $this->boardMessage = $boardMessage;

        return $this;
    }

    /**
     * @return int
     */
    public function getBoardMessage(): int
    {
        return $this->boardMessage;
    }
}