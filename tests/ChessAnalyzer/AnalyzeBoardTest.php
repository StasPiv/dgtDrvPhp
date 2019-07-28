<?php

namespace StasPiv\DgtDrvPhp\Tests\ChessAnalyzer;

use StasPiv\DgtDrvPhp\BufferAnalyzer\ChessAnalyzer;
use StasPiv\DgtDrvPhp\Stream\NullStream;

class AnalyzeBoardTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChessAnalyzer */
    private $chessAnalyzer;

    protected function setUp()
    {
        $this->chessAnalyzer = new ChessAnalyzer(new NullStream(), new \FenParser0x88());
    }

    public function testMakeMoveAndResetValidMovesWhenMoveFoundAndBoardUpdated()
    {
        $actions = $this->chessAnalyzer->getResultForAnalyzeBoard(true, true);

        $this->assertEquals(
            [ChessAnalyzer::MAKE_MOVE, ChessAnalyzer::HANDLE_MOVE_COMPLETED, ChessAnalyzer::RESET_VALID_MOVES],
            $actions
        );
    }

    public function testMakeMoveWhenMoveFoundAndBoardNotUpdated()
    {
        $actions = $this->chessAnalyzer->getResultForAnalyzeBoard(true, false);

        $this->assertEquals(
            [ChessAnalyzer::MAKE_MOVE, ChessAnalyzer::HANDLE_MOVE_COMPLETED],
            $actions
        );
    }

    public function testResetValidMovesWhenMoveNotFoundAndBoardUpdated()
    {
        $actions = $this->chessAnalyzer->getResultForAnalyzeBoard(false, true);

        $this->assertEquals(
            [ChessAnalyzer::RESET_VALID_MOVES],
            $actions
        );
    }

    public function testDoNothingWhenMoveNotFoundAndBoardNotUpdated()
    {
        $actions = $this->chessAnalyzer->getResultForAnalyzeBoard(false, false);

        $this->assertEquals(
            [],
            $actions
        );
    }
}
