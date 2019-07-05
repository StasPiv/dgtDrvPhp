<?php

namespace StasPiv\DgtDrvPhp\BufferAnalyzer;

use StasPiv\DgtDrvPhp\BufferAnalyzer;

/**
 * Class FileOutputAnalyzer
 * @package StasPiv\DgtDrvPhp\BufferAnalyzer
 */
class FileRawOutputAnalyzer implements BufferAnalyzer
{
    private $fileName;

    /**
     * FileOutputAnalyzer constructor.
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

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
        file_put_contents($this->fileName, implode('|', $buffer) . PHP_EOL, FILE_APPEND);
    }
}