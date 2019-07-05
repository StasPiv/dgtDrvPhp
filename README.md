Basic usage:

```php
#!/usr/bin/env php
<?php

error_reporting(E_ALL & ~E_NOTICE);

use StasPiv\DgtDrvPhp\BufferAnalyzer\ChessAnalyzer;
use StasPiv\DgtDrvPhp\BufferAnalyzer\SomeNewHandler;
use StasPiv\DgtDrvPhp\Stream;
use StasPiv\DgtDrvPhp\StreamReader\DgtBoardStreamReader;

require_once 'vendor/autoload.php';

$stream = new Stream();
$streamReader = new DgtBoardStreamReader();

$chessAnalyzer = new ChessAnalyzer(
    $stream,
    new FenParser0x88(),
    in_array('--rotated', $argv) ? 0 : 1,
    in_array('--black', $argv) ? 'b' : 'w'
);

$streamReader->addAnalyzer($chessAnalyzer);
$stream->attach($streamReader);

// Now we can add handlers to ChessAnalyzer objects
// They should implement HandlerInterface with such methods
//
// public function handlePieceAdded(string $square, string $piece);
// public function handlePieceRemoved(string $square);
// public function handleBoardUpdated(string $newFen, &$updatedFen = null) : bool;
// public function handleLegalMoveCompleted(array $move, string $moveNotation, string $fenBefore, string $fenAfter) : bool ;


$handler = new SomeNewHandler();
$chessAnalyzer->addHandler($handler);

// Now start stream. $handler will receive all needed updates

$stream->start();