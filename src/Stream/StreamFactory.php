<?php

namespace StasPiv\DgtDrvPhp\Stream;

use StasPiv\DgtDrvPhp\Exception\OptionsRequiredException;
use StasPiv\DgtDrvPhp\Exception\UnknownStreamTypeException;
use StasPiv\DgtDrvPhp\Stream;

class StreamFactory
{
    public static function create(string  $type, array $options = [])
    {
        switch ($type) {
            case StreamType::BUFFERED:
                if (!isset($options['ws'])) {
                    throw new OptionsRequiredException('Required option: ws');
                }

                return new BufferedStream($options['ws']);
            case StreamType::CU:
                return new Stream(isset($options['connectionType']) ? $options['connectionType'] : ConnectionType::BLUETOOTH);
            default:
                throw new UnknownStreamTypeException();
        }
    }
}