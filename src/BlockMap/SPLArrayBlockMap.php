<?php


namespace makadev\BitSet\BlockMap;

use makadev\BitSet\Contract\BlockMap;
use SplFixedArray;

class SPLArrayBlockMap implements BlockMap {

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
     * @var SplFixedArray<int> $internalBlockMap
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
        return $this->internalBlockMap[$position];
    }

    /**
     * Write Block at given position.
     *
     * @param int $position
     * @param int $block Block to be written
     */
    public function writeBlockMap(int $position, int $block): void {
        $this->internalBlockMap[$position] = $block;
    }

    /**
     * BlockMap constructor with given bit length
     *
     * @param int $blockLength
     */
    public function __construct(int $blockLength) {
        $this->blockLength = $blockLength;
        $this->internalBlockMap = new SplFixedArray($blockLength);
        for ($i = 0; $i < $this->blockLength; $i++) {
            $this->internalBlockMap[$i] = 0;
        }
    }

    /**
     * Clone and make sure the internal BlockMap is duplicated correctly.
     */
    public function __clone() {
        $cloneMap = $this->internalBlockMap;
        $this->internalBlockMap = new SplFixedArray($this->blockLength);
        for ($i = 0; $i < $this->blockLength; $i++) {
            $this->internalBlockMap[$i] = $cloneMap[$i];
        }
    }
}
