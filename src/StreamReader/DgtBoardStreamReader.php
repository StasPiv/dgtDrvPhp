<?php


namespace StasPiv\DgtDrvPhp\StreamReader;

use SplSubject;
use StasPiv\DgtDrvPhp\BufferAnalyzer;
use StasPiv\DgtDrvPhp\Stream;
use StasPiv\DgtDrvPhp\StreamReader;

class DgtBoardStreamReader implements StreamReader
{
    /* Message sent to the board */
    const SEND_RESET         = 0x40; // @
    const SEND_CLK           = 0x41; // A
    const SEND_BRD           = 0x42; // B
    const SEND_UPDATE        = 0x43; // C
    const SEND_UPDATE_BRD    = 0x44; // D
    const SEND_SERIALNR      = 0x45; // E
    const SEND_BUSADDRESS    = 0x46; // F
    const SEND_TRADEMARK     = 0x47; // G
    const SEND_VERSION       = 0x4d; // H
    const SEND_UPDATE_NICE   = 0x4b; // I
    const SEND_EE_MOVES      = 0x49; // J

    const MESSAGE_BOARD      = 0x86;
    const MESSAGE_UPDATE     = 0x8d;
    const MESSAGE_MOVE       = 0x8e;

    const BUFFER_TYPE_BOARD  = 0;
    const BUFFER_TYPE_UPDATE = 1;
    const BUFFER_TYPE_MOVE   = 2;

    const PART_SIZE_TYPE     = 3;
    const PART_SIZE_BOARD    = 64 + self::PART_SIZE_TYPE;
    const PART_SIZE_UPDATE   = 7  + self::PART_SIZE_TYPE;
    const PART_SIZE_MOVE     = 2  + self::PART_SIZE_TYPE;

    private $partSize        = self::PART_SIZE_MOVE;
    private $messageType     = self::MESSAGE_UPDATE;
    private $buffer          = [];
    private $bufferBoard     = [];
    private $bufferCounter   = 0;

    /** @var BufferAnalyzer[]|array */
    private $analyzers = [];

    public function update(SplSubject $stream)
    {
        if (!$stream instanceof Stream) {
            return;
        }

        $boardMessage = $stream->getBoardMessage();

        switch ($boardMessage) {
            case self::MESSAGE_BOARD:
                $this->partSize = self::PART_SIZE_BOARD;
                break;
            case self::MESSAGE_MOVE:
                $this->partSize = self::PART_SIZE_MOVE;
                break;
        }

        if (in_array($boardMessage, [self::MESSAGE_BOARD, self::MESSAGE_MOVE])) {
            $this->flushBuffer();
            $this->setMessageType($boardMessage);
        }

        $this->buffer[$this->bufferCounter++] = $boardMessage;

        if ($this->bufferCounter == $this->partSize) {
            $this->buffer = array_splice($this->buffer, self::PART_SIZE_TYPE);

            foreach ($this->analyzers as $analyzer) {
                switch ($this->getMessageType()) {
                    case self::MESSAGE_MOVE:
                        $analyzer->analyzeMove($this->buffer);
                        break;
                    case self::MESSAGE_BOARD:
                        $this->bufferBoard = $this->buffer;
                        $analyzer->analyzeBoard($this->buffer);
                        break;
                }
            }
        }

        if (in_array($this->buffer, [[142,0,5,0], [142,0,5,3]])) {
            $stream->write(DgtBoardStreamReader::SEND_BRD);
        }
    }

    /**
     * @return int
     */
    public function getMessageType(): int
    {
        return $this->messageType;
    }

    /**
     * @param int $messageType
     * @return DgtBoardStreamReader
     */
    public function setMessageType(int $messageType): DgtBoardStreamReader
    {
        $this->messageType = $messageType;
        return $this;
    }

    private function flushBuffer(): void
    {
        $this->bufferCounter = 0;
        $this->buffer = [];
    }

    public function addAnalyzer(BufferAnalyzer $analyzer)
    {
        $this->analyzers[] = $analyzer;

        return $this;
    }

    public function removeAnalyzer(BufferAnalyzer $analyzer)
    {
        if (($key = array_search($analyzer, $this->analyzers)) !== false) {
            unset($this->analyzers[$key]);
        }
    }
}