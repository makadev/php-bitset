<?php


namespace makadev\BitSet\BitMap;

use makadev\BitSet\Contract\BitMap;
use makadev\BitSet\Contract\BlockMap;
use OutOfBoundsException;
use RuntimeException;

abstract class BlockBitMap implements BitMap {

    /**
     * Return the amount of bits each block can store
     *
     * @return int
     */
    public function getBitsPerBlock(): int {
        return $this->blockMap::BytesPerBlock() * 8;
    }

    /**
     * Internal BlockMap holding the BitMap
     *
     * @var BlockMap $blockMap
     */
    protected $blockMap;

    /**
     * Return the length in Blocks for the underlying BlockMap
     *
     * @return int
     */
    public function getBlockLength(): int {
        return $this->blockMap->getBlockLength();
    }

    /**
     * Length in Bits
     *
     * @var int $bitLength
     */
    protected $bitLength;

    /**
     * Get the Length of this BitMap in Bits
     *
     * @return int
     */
    public function getBitLength(): int {
        return $this->bitLength;
    }

    /**
     * Helper throwing Out of Bounds Exception when the $position doesn't fit the bitLength of this Structure
     *
     * @param integer $position
     * @return void
     * @throws OutOfBoundsException if $position is < 0 or >= bit length
     */
    protected function assertInBounds(int $position) {
        if (($position < 0) || ($position >= $this->bitLength)) {
            throw new OutOfBoundsException();
        }
    }

    /**
     * Helper throwing Out of Bounds Exception when the $position doesn't fit the blockLength of this Structure
     *
     * @param integer $position
     * @return void
     * @throws OutOfBoundsException if $position is < 0 or >= block length
     */
    protected function assertInBlockBounds(int $position) {
        if (($position < 0) || ($position >= $this->blockMap->getBlockLength())) {
            throw new OutOfBoundsException();
        }
    }

    /**
     * Helper throwing an Exception when the bit length doesn't match.
     *
     * @param BitMap $bitMap
     * @return void
     */
    protected function assertBitLengthMatch(BitMap $bitMap) {
        if ($this->bitLength !== $bitMap->getBitLength()) {
            throw new OutOfBoundsException();
        }
    }

    /**
     * Return the position of the block which stores the given bit.
     *
     * @param int $bit
     * @return int
     */
    public function bitToIndex(int $bit): int {
        return intdiv($bit, $this->getBitsPerBlock());
    }

    /**
     * Return the position of the bit in it's respective block.
     *
     * @param int $bit
     * @return int
     */
    public function bitToBlockIndex(int $bit): int {
        return $bit % $this->getBitsPerBlock();
    }

    /**
     * BitMap constructor with given bit length
     *
     * @param int $bitLength
     * @param BlockMap $blockMap
     */
    public function __construct(int $bitLength, BlockMap $blockMap) {
        $this->bitLength = $bitLength;
        $this->blockMap = $blockMap;
        $bitsPerBlock = $this->blockMap->bytesPerBlock() * 8;
        $numBits = $this->blockMap->getBlockLength() * $bitsPerBlock;
        if ($bitLength > $numBits) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException("Insufficient BlockMap length for given bitLength");
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Clone and make sure the internal Bitmap is duplicated correctly.
     */
    public function __clone() {
        $this->blockMap = clone $this->blockMap;
    }

    /**
     * Set bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 0 to 1, otherwise false
     * @throws OutOfBoundsException if position is invalid
     */
    public function set(int $position): bool {
        $this->assertInBounds($position);
        $index = $this->bitToIndex($position);
        $shift = $this->bitToBlockIndex($position);
        $setBit = 1 << $shift;
        $block = $this->blockMap->readBlockMap($index);
        $bitClean = !(($block & $setBit) === $setBit);
        if ($bitClean) {
            $this->blockMap->writeBlockMap($index, $block | $setBit);
        }
        return $bitClean;
    }

    /**
     * Unset bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 1 to 0, otherwise false
     * @throws OutOfBoundsException if position is invalid
     */
    public function unset(int $position): bool {
        $this->assertInBounds($position);
        $index = $this->bitToIndex($position);
        $shift = $this->bitToBlockIndex($position);
        $unsetBit = 1 << $shift;
        $block = $this->blockMap->readBlockMap($index);
        $bitSet = ($block & $unsetBit) === $unsetBit;
        if ($bitSet) {
            $this->blockMap->writeBlockMap($index, $block ^ $unsetBit);
        }
        return $bitSet;
    }

    /**
     * Check if bit at given position is set
     *
     * @param int $position
     * @return bool true if bit at given position is set, false if it's not set
     * @throws OutOfBoundsException if position is invalid
     */
    public function test(int $position): bool {
        $this->assertInBounds($position);
        $index = $this->bitToIndex($position);
        $shift = $this->bitToBlockIndex($position);
        $testBit = 1 << $shift;
        return ($this->blockMap->readBlockMap($index) & $testBit) === $testBit;
    }

    /**
     * Execute given callable with signature function(int $block, int $position): int|false
     * for each block sized block of the internal bitmap.
     *
     * If given function returns false, iteration will be stopped and eachBlock returns with false.
     * If given function returns an int, the block at current $position will be set to that.
     * Any other result is ignored.
     *
     * Getting/Setting blocks behaves like getBlock/setBlock
     * @param callable(int, int): (int|bool) $f
     * @return bool true if iteration was complete, false otherwise
     * @see getBlock()
     * @see setBlock()
     *
     */
    public function eachBlock(callable $f): bool {
        for ($i = 0; $i < $this->blockMap->getBlockLength(); $i++) {
            $block = $this->getBlock($i);
            $update = $f($block, $i);
            if ($update === false) {
                return false;
            }
            if (is_int($update)) {
                $this->setBlock($i, $update);
            }
        }
        return true;
    }

    /**
     * Get bitmap block at given position.
     *
     * @param int $position
     * @return int
     * @throws OutOfBoundsException if position is invalid
     */
    public function getBlock(int $position): int {
        $this->assertInBlockBounds($position);
        $fullblockMask = ~((~0) << $this->getBitsPerBlock());
        $result = $this->blockMap->readBlockMap($position) & $fullblockMask;
        if ($position === $this->blockMap->getBlockLength() - 1) {
            $lastBlockUpperBit = $this->bitLength % $this->getBitsPerBlock();
            if ($lastBlockUpperBit !== 0) {
                $upperBit = 1 << ($lastBlockUpperBit - 1);
                $mask = ~((~($upperBit)) + 1) | $upperBit;
                $result = $result & $mask;
            }
        }
        return $result;
    }

    /**
     * Set bitmap block at given position.
     *
     * @param int $position
     * @param int $block
     * @return bool true if there was an actual change, false otherwise
     * @throws OutOfBoundsException if position is invalid
     */
    public function setBlock(int $position, int $block): bool {
        $this->assertInBlockBounds($position);
        $before = $this->blockMap->readBlockMap($position);
        if ($position === $this->blockMap->getBlockLength() - 1) {
            $lastBlockUpperBit = $this->bitLength % $this->getBitsPerBlock();
            if ($lastBlockUpperBit !== 0) {
                $upperBit = 1 << ($lastBlockUpperBit - 1);
                $mask = ~((~($upperBit)) + 1) | $upperBit;
                $block = $block & $mask;
            }
        }
        if ($before !== $block) {
            $this->blockMap->writeBlockMap($position, $block);
            return true;
        }
        return false;
    }

    /**
     * Set all bits in a certain range
     *
     * @param int $from
     * @param int $to
     * @return bool true if at least one bit was changed from 0 to 1, otherwise false
     * @throws OutOfBoundsException if from or to is invalid
     */
    public function setRange(int $from, int $to): bool {
        $this->assertInBounds($from);
        $this->assertInBounds($to);
        // special case, wrong input order, ignore
        if ($from > $to) {
            return false;
        }
        // case 1.: from === to (1 bit)
        if ($from === $to) {
            return $this->set($from);
        }
        $fullblockMask = ~((~0) << $this->getBitsPerBlock());
        // f.e. bit 4 (0 index) = ~b00010000 -> b11101111 +1 -> b11110000
        $fromBlock = ((~(1 << $this->bitToBlockIndex($from))) + 1) & $fullblockMask;
        // f.e. bit 4 (0 index) = ~b00010000 -> b11101111 +1 -> ~b11110000 | b00010000 -> b00011111
        $toBit = 1 << $this->bitToBlockIndex($to);
        $toBlock = (~((~($toBit)) + 1) | $toBit) & $fullblockMask;
        $startWord = $this->bitToIndex($from);
        $endWord = $this->bitToIndex($to);
        // case 2.: from and to are in the same block
        if ($startWord === $endWord) {
            $oldWord = $this->blockMap->readBlockMap($startWord) & $fullblockMask;
            $update = $oldWord | ($fromBlock & $toBlock);
            if ($update !== $oldWord) {
                $this->blockMap->writeBlockMap($startWord, $update);
                return true;
            }
            return false;
        }
        // case 3.: from and to are in the different blocks where to = from+1
        $changed = false;
        $oldWord = $this->blockMap->readBlockMap($startWord) & $fullblockMask;
        if (($oldWord | $fromBlock) !== $oldWord) {
            $this->blockMap->writeBlockMap($startWord, $oldWord | $fromBlock);
            $changed = true;
        }
        $oldWord = $this->blockMap->readBlockMap($endWord) & $fullblockMask;
        if (($oldWord | $toBlock) !== $oldWord) {
            $this->blockMap->writeBlockMap($endWord, $oldWord | $toBlock);
            $changed = true;
        }
        // case 4.: from and to are in the different blocks where to = from+n with n > 1
        while (++$startWord < $endWord) {
            $changed = $changed || ($fullblockMask !== ($this->blockMap->readBlockMap($startWord) & $fullblockMask));
            $this->blockMap->writeBlockMap($startWord, $fullblockMask);
        }
        return $changed;
    }

}
