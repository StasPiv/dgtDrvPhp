#!/usr/bin/env php
<?php

error_reporting(E_ALL & ~E_NOTICE);

use StasPiv\DgtDrvPhp\BufferAnalyzer\ChessAnalyzer;
use StasPiv\DgtDrvPhp\BufferAnalyzer\ConsoleRawOutputAnalyzer;
use StasPiv\DgtDrvPhp\BufferAnalyzer\FileRawOutputAnalyzer;
use StasPiv\DgtDrvPhp\Stream;
use StasPiv\DgtDrvPhp\StreamReader\DgtBoardStreamReader;

require_once 'vendor/autoload.php';

$stream = new Stream();
$streamReader = new DgtBoardStreamReader();

$analyzers = [
//    new ConsoleRawOutputAnalyzer(),
//    new FileRawOutputAnalyzer('output.txt'),
    new ChessAnalyzer($stream, new FenParser0x88()),
];

foreach ($analyzers as $analyzer) {
    $streamReader->addAnalyzer($analyzer);
}

$stream->attach($streamReader);
$stream->start();