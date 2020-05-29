<?php

namespace StasPiv\DgtDrvPhp;

use Throwable;
use Board0x88Config;

class FenGenerator
{
    /**
     * @param array $buffer
     *
     * @return string
     */
    public function bufferToFen(array $buffer, bool $boardRotated = false): string
    {
        if ($boardRotated) {
            $buffer = array_reverse($buffer);
        }

        $squareCounter = $emptyCounter = 0;

        $lines = [];
        $line = '';

        foreach ($buffer as $piece) {
            if (is_null($piece)) {
                $emptyCounter++;
            } else {
                $line .= $emptyCounter > 0 ? $emptyCounter : '';
                try {
                    $line .= strtoupper(Board0x88Config::$pieceMapping[$piece]);
                } catch (Throwable $exception) {
                    return '';
                }
                $emptyCounter = 0;
            }

            if (++$squareCounter % 8 === 0) {
                $line .= $emptyCounter > 0 ? $emptyCounter : '';
                $lines[] = $line;
                $line = '';
                $squareCounter = $emptyCounter = 0;
            }
        }

        return implode('/', $lines);
    }
}