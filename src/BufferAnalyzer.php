<?php

namespace StasPiv\DgtDrvPhp;

/**
 * Interface BufferAnalyzer
 * @package StasPiv\DgtDrvPhp
 */
interface BufferAnalyzer
{
    /**
     * @param array $buffer
     */
    public function analyzeUpdate(array $buffer) : void;

    /**
     * @param array $buffer
     * @param bool  $analyzeExceptions
     */
    public function analyzeBoard(array $buffer, bool $analyzeExceptions = false) : void;

    /**
     * @param array $buffer
     */
    public function analyzeMove(array $buffer) : void;
}