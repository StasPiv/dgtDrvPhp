<?php


namespace StasPiv\DgtDrvPhp\BufferAnalyzer;


class SomeNewHandler implements HandlerInterface
{
    public function handlePieceAdded(string $square, string $piece)
    {
        var_dump(func_get_args());
    }

    public function handlePieceRemoved(string $square)
    {
        var_dump(func_get_args());
    }

    public function handleBoardUpdated(string $newFen, string $whiteBelowFen, &$updatedFen = null, $theSameFen = false): bool
    {
        var_dump(func_get_args());
        return false;
    }

    public function handleLegalMoveCompleted(array $move, string $moveNotation, string $fenBefore, string $fenAfter): bool
    {
        var_dump(func_get_args());
        return true;
    }

}