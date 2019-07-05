<?php


namespace StasPiv\DgtDrvPhp\BufferAnalyzer;

use StasPiv\DgtDrvPhp\BufferAnalyzer;

class ConsoleRawOutputAnalyzer implements BufferAnalyzer
{
    public function analyzeUpdate(array $buffer): void
    {
        // TODO: Implement analyzeUpdate() method.
    }

    public function analyzeBoard(array $buffer): void
    {
        $this->printBuffer($buffer);
    }

    public function analyzeMove(array $buffer): void
    {
        $this->printBuffer($buffer);
    }

    private function printBuffer(array $buffer)
    {
        echo implode('|', $buffer) . PHP_EOL;
    }

}