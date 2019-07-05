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
     */
    public function analyzeBoard(array $buffer) : void;

    /**
     * @param array $buffer
     */
    public function analyzeMove(array $buffer) : void;
}