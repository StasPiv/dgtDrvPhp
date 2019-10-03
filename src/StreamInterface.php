<?php

namespace StasPiv\DgtDrvPhp;

use SplSubject;

interface StreamInterface extends SplSubject
{
    public function start(callable $callable = null);

    public function write(int $number);

    /**
     * @return int
     */
    public function getBoardMessage(): int;
}