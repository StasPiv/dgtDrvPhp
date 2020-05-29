<?php

namespace StasPiv\DgtDrvPhp\Exception;

use Exception;
use Throwable;

class OptionsRequiredException extends Exception
{
    public function __construct($message = "Options required", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}