<?php

namespace StasPiv\DgtDrvPhp\BufferAnalyzer;

use FenParser0x88;
use FenParser0x88Exception;
use StasPiv\DgtDrvPhp\BufferAnalyzer;
use StasPiv\DgtDrvPhp\Stream;
use StasPiv\DgtDrvPhp\StreamReader\DgtBoardStreamReader;

/**
 * Class ChessAnalyzer
 * @package StasPiv\DgtDrvPhp\BufferAnalyzer
 */
class ChessAnalyzer implements BufferAnalyzer
{
    const PIECE_EMPTY       = 0x00;
    const PIECE_WPAWN       = 0x01;
    const PIECE_WROOK       = 0x02;
    const PIECE_WKNIGHT     = 0x03;
    const PIECE_WBISHOP     = 0x04;
    const PIECE_WKING       = 0x05;
    const PIECE_WQUEEN      = 0x06;
    const PIECE_BPAWN       = 0x07;
    const PIECE_BROOK       = 0x08;
    const PIECE_BKNIGHT     = 0x09;
    const PIECE_BBISHOP     = 0x0a;
    const PIECE_BKING       = 0x0b;
    const PIECE_BQUEEN      = 0x0c;

    /** @var Stream */
    private $stream;

    private $boardRotated = true;

    /**
     * @var FenParser0x88
     */
    private $fenParser;
    private $moveInProgress = false;

    private $validMoveFens = [];

    private $debug = false;

    /**
     * @var string
     */
    private $moveToColor = 'w';

    /**
     * @var string
     */
    private $castleString = 'KQkq';

    /**
     * @var string
     */
    private $enPassantSquare = '-';

    /**
     * @var string
     */
    private $moveInformation = '0 0';

    private $boardFen = '';

    private $lastMove = [];

    private $lastMoveNotation = '';

    /** @var HandlerInterface[]|array */
    private $handlers;
    
    /**
     * ChessAnalyzer constructor.
     * @param Stream $stream
     * @param FenParser0x88 $fenParser
     * @param bool $boardRotated
     * @param string $moveToColor
     * @param string $castleString
     * @param string $enPassantSquare
     * @param string $moveInformation
     */
    public function __construct(Stream $stream, FenParser0x88 $fenParser, bool $boardRotated = true, string $moveToColor = 'w', string $castleString = 'KQkq', string $enPassantSquare = '-', string $moveInformation = '0 0')
    {
        $this->stream = $stream;
        $this->boardRotated = $boardRotated;
        $this->fenParser = $fenParser;
        $this->moveToColor = $moveToColor;
        $this->castleString = $castleString;
        $this->enPassantSquare = $enPassantSquare;
        $this->moveInformation = $moveInformation;

        $stream->write(DgtBoardStreamReader::SEND_UPDATE_BRD);
        $stream->write(DgtBoardStreamReader::SEND_BRD);
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return ChessAnalyzer
     */
    public function setDebug(bool $debug): ChessAnalyzer
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return array
     */
    public function getLastMove(): array
    {
        return $this->lastMove;
    }

    /**
     * @param array $lastMove
     * @return ChessAnalyzer
     */
    public function setLastMove(array $lastMove): ChessAnalyzer
    {
        $this->lastMove = $lastMove;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastMoveNotation(): string
    {
        return $this->lastMoveNotation;
    }

    /**
     * @param string $lastMoveNotation
     * @return ChessAnalyzer
     */
    public function setLastMoveNotation(string $lastMoveNotation): ChessAnalyzer
    {
        $this->lastMoveNotation = $lastMoveNotation;
        return $this;
    }

    /**
     * @param HandlerInterface $handler
     * @return $this
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * @param HandlerInterface $handler
     * @return $this
     */
    public function removeHandler(HandlerInterface $handler)
    {
        if (($key = array_search($handler, $this->handlers)) !== false) {
            unset($this->handlers[$key]);
        }

        return $this;
    }

    /**
     * @param array $buffer
     */
    public function analyzeUpdate(array $buffer): void
    {
        // TODO: Implement analyzeUpdate() method.
    }

    /**
     * @param string $moveToColor
     * @return ChessAnalyzer
     */
    public function setMoveToColor(string $moveToColor): ChessAnalyzer
    {
        $this->moveToColor = $moveToColor;
        return $this;
    }

    /**
     * @param array $buffer
     * @throws \Exception
     */
    public function analyzeBoard(array $buffer): void
    {
        if ($this->boardRotated) {
            $buffer = array_reverse($buffer);
        }

        $fen = explode(' ', $this->bufferToFen($buffer))[0];

        if ($this->handleBoardUpdated($fen)) {
            $this->moveInProgress = false;
        }
        
        if ($this->moveInProgress) {
            if (isset($this->validMoveFens[$fen])) {
                $move = $this->validMoveFens[$fen];
                $fenBefore = $this->fenParser->getFen();
                try {
                    $this->fenParser->move($move);
                } catch (FenParser0x88Exception $exception) {
                    echo 'parser fen ' . $this->fenParser->getFen() . PHP_EOL;
                    // not valid chess move
                    echo $exception->getMessage() . PHP_EOL;
                    return;
                }
                echo 'move completed: ' . $this->fenParser->getNotation() . PHP_EOL;
                $this->setLastMove($move)->setLastMoveNotation($this->fenParser->getNotation());
                
                $this->handleLegalMoveCompleted($move, $fenBefore);
                
                $this->moveInProgress = false;
            }

            return;
        }

        if (empty($this->fenParser->getNotation())) {
            $this->fenParser->setFen($fen . $this->getFenSuffix());
        }

        $this->moveInProgress = true;
        $this->resetValidMoves();
    }

    /**
     * @param array $buffer
     */
    public function analyzeMove(array $buffer): void
    {
        $pieceNotation = $this->getPieceNotation($buffer[1]);
        $square = $this->getSquare($buffer[0]);

        if (!empty($pieceNotation)) {
            if ($this->isDebug()) {
                echo $pieceNotation . ' added on ' . $square . PHP_EOL;
            }
            foreach ($this->handlers as $handler) {
                $handler->handlePieceAdded($square, $pieceNotation);
            }
        } else {
            foreach ($this->handlers as $handler) {
                $handler->handlePieceRemoved($square);
            }
            if ($this->isDebug()) {
                echo 'piece removed from ' . $square . PHP_EOL;
            }
        }

        $this->stream->write(DgtBoardStreamReader::SEND_BRD);
    }

    /**
     * @param int $piece
     * @return string
     */
    private function getPieceNotation(int $piece) : string
    {
        switch ($piece) {
            case self::PIECE_EMPTY:
                return '';
            case self::PIECE_WPAWN:
                return 'P';
            case self::PIECE_WROOK:
                return 'R';
            case self::PIECE_WKNIGHT:
                return 'N';
            case self::PIECE_WBISHOP:
                return 'B';
            case self::PIECE_WKING:
                return 'K';
            case self::PIECE_WQUEEN:
                return 'Q';
            case self::PIECE_BPAWN:
                return 'p';
            case self::PIECE_BROOK:
                return 'r';
            case self::PIECE_BKNIGHT:
                return 'n';
            case self::PIECE_BBISHOP:
                return 'b';
            case self::PIECE_BKING:
                return 'k';
            case self::PIECE_BQUEEN:
                return 'q';
            default:
                throw new \RuntimeException('Unknown chess piece: ' . $piece);
        }
    }

    /**
     * @param int $square
     * @return string
     */
    private function getSquare(int $square)
    {
        if ($this->boardRotated) {
            $square = 63 - $square;
        }

        return ('abcdefgh'){$square % 8}.(8 - (int)($square / 8));
    }

    /**
     * @param array $buffer
     * @return string
     */
    private function bufferToFen(array $buffer) : string
    {
        $squareCounter = $emptyCounter = 0;

        $lines = [];
        $line = '';

        foreach ($buffer as $piece) {
            if ($piece === self::PIECE_EMPTY) {
                $emptyCounter++;
            } else {
                $line .= $emptyCounter > 0 ? $emptyCounter : '';
                $line .= $this->getPieceNotation($piece);
                $emptyCounter = 0;
            }

            if (++$squareCounter % 8 === 0) {
                $line .= $emptyCounter > 0 ? $emptyCounter : '';
                $lines[] = $line;
                $line = '';
                $squareCounter = $emptyCounter = 0;
            }
        }

        $this->boardFen = implode('/', $lines);

        return $this->boardFen . $this->getFenSuffix();
    }

    /**
     * @return string
     */
    private function getFenSuffix(): string
    {
        return ' ' . $this->moveToColor . ' ' . $this->castleString . ' ' . $this->enPassantSquare . ' ' . $this->moveInformation;
    }

    /**
     * @param array $validMove
     * @throws \Exception
     */
    private function addValidMove(array $validMove): void
    {
        $fenBefore = $this->fenParser->getFen();
        try {
            $this->fenParser->move($validMove);
        } catch (FenParser0x88Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
        $this->validMoveFens[explode(' ', $this->fenParser->getFen())[0]] = $validMove;
        $this->fenParser->setFen($fenBefore);
    }

    private function resetValidMoves(string $fen = ''): void
    {
        if (!empty($fen)) {
            $this->fenParser->setFen($fen);
        }

        foreach ($this->fenParser->getValidMovesBoardCoordinates() as $from => $validCoordinate) {
            foreach ($validCoordinate as $to) {
                $pieceFrom = $this->fenParser->getPieceOnSquareBoardCoordinate($from);
                $validMove = [
                    'from' => $from,
                    'to' => $to,
                ];

                if ($pieceFrom['type'] === 'pawn' && in_array($to{1}, [1, 8])) { // promotion
                    $validPromotions = ['q', 'r', 'n', 'b'];
                    foreach ($validPromotions as $validPromotion) {
                        $validMove['promoteTo'] = $validPromotion;
                        $this->addValidMove($validMove);
                    }
                    continue;
                }

                $this->addValidMove($validMove);
            }
        }
    }

    /**
     * @param string $fen
     * @return bool
     */
    private function handleBoardUpdated(string $fen): bool
    {
        $ret = true;
        
        foreach ($this->handlers as $handler) {
            if ($ret &= $handler->handleBoardUpdated($fen, $updatedFen)) {
                $this->resetValidMoves($updatedFen ?: $fen . $this->getFenSuffix());
            }
        }
        
        return $ret;
    }

    /**
     * @param $move
     * @param string $fenBefore
     */
    private function handleLegalMoveCompleted($move, string $fenBefore): void
    {
        foreach ($this->handlers as $handler) {
            if (!$handler->handleLegalMoveCompleted($move, $this->fenParser->getNotation(), $fenBefore, $this->fenParser->getFen())) {
                $this->moveInProgress = false;
                $this->resetValidMoves($fenBefore);
            }

        }
    }

}