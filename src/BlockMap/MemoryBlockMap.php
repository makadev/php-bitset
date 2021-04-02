<?php


namespace makadev\BitSet\BlockMap;

use makadev\BitSet\Contract\BlockMap;
use makadev\Buffer\RWBuffer\RWMemoryBuffer;
use RuntimeException;

class MemoryBlockMap implements BlockMap {

    /**
     * Returning the Block Size in Bytes
     *
     * @return int
     */
    public static function BytesPerBlock(): int {
        return (int)PHP_INT_SIZE;
    }

    /**
     * Internal
     *
     * @var RWMemoryBuffer $internalBlockMap
     */
    protected $internalBlockMap;

    /**
     * Length of BlockMap in blocks
     *
     * @var int $blockLength
     */
    protected $blockLength;

    /**
     * Get the length of the BlockMap in blocks
     *
     * @return int
     */
    public function getBlockLength(): int {
        return $this->blockLength;
    }

    /**
     * Read the Block from given position.
     *
     * @param int $position
     * @return int Block at given position
     */
    public function readBlockMap(int $position): int {
        $blockSize = static::BytesPerBlock();
        $this->internalBlockMap->setPosition($blockSize * $position);
        $binString = $this->internalBlockMap->read($blockSize);
        // @codeCoverageIgnoreStart
        if (strlen($binString) !== $blockSize) {
            throw new RuntimeException("Unexpected number of bytes read");
        }
        // @codeCoverageIgnoreEnd
        return unpack($blockSize === 8 ? 'q' : 'i', $binString)[1];
    }

    /**
     * Write Block at given position.
     *
     * @param int $position
     * @param int $block Block to be written
     */
    public function writeBlockMap(int $position, int $block): void {
        $blockSize = static::BytesPerBlock();
        $this->internalBlockMap->setPosition($blockSize * $position);
        $binString = pack($blockSize === 8 ? 'q' : 'i', $block);
        $written = $this->internalBlockMap->write($binString, $blockSize);
        // @codeCoverageIgnoreStart
        if ($written !== $blockSize) {
            throw new RuntimeException("Unexpected number of bytes written");
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * BlockMap constructor with given bit length
     *
     * @param int $blockLength
     */
    public function __construct(int $blockLength) {
        // @codeCoverageIgnoreStart
        if (PHP_INT_SIZE !== 8 && PHP_INT_SIZE !== 4) {
            throw new RuntimeException("Unexpected Integer Size");
        }
        // @codeCoverageIgnoreEnd
        $this->blockLength = $blockLength;
        // RWMemoryBuffer will zero the memory, so no need to handle that
        $this->internalBlockMap = new RWMemoryBuffer(static::BytesPerBlock() * $this->blockLength);
    }

    /**
     * Clone and make sure the internal BlockMap is duplicated correctly.
     */
    public function __clone() {
        $cloneBuffer = $this->internalBlockMap;
        $this->internalBlockMap = new RWMemoryBuffer(static::BytesPerBlock() * $this->blockLength);
        $maxBlock = 1024 * 5;
        $cloneBuffer->setPosition(0);
        while ($cloneBuffer->getPosition() < $cloneBuffer->getSize()) {
            $temp = $cloneBuffer->read(min($maxBlock, $cloneBuffer->getSize()));
            $written = $this->internalBlockMap->write($temp, strlen($temp));
            // this shouldn't happen in memory
            if ($written !== strlen($temp)) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException("Unexpected number of bytes written");
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
