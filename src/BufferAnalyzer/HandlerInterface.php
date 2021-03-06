<?php

namespace StasPiv\DgtDrvPhp\BufferAnalyzer;


interface HandlerInterface
{
    public function handlePieceAdded(string $square, string $piece);

    public function handlePieceRemoved(string $square);

    public function handleBoardUpdated(string $newFen, string $whiteBelowFen, &$updatedFen = null, $theSameFen = false) : bool;

    public function handleLegalMoveCompleted(array $move, string $moveNotation, string $fenBefore, string $fenAfter, &$resetAfterLegalMove = false) : bool ;
}