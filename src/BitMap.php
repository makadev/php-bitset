<?php


namespace makadev\BitSet;

use OutOfBoundsException;
use SplFixedArray;

/**
 * Class for (dense) bitmap manipulation.
 *
 * @package makadev\BitSet\BitMap
 */
class BitMap {

    /**
     * Nr. Bits per Machine Word
     */
    public const BitPerWord = PHP_INT_SIZE * 8;

    /**
     * Length in Bits
     *
     * @var int $bitLength
     */
    private int $bitLength;

    /**
     * Get the Length of this BitMap in Bits
     *
     * @return int
     */
    public function getBitLength(): int {
        return $this->bitLength;
    }

    /**
     * Length in Words (for internal Array of Words)
     */
    private int $wordLength;

    /**
     * Get the Length of BitMap in Words
     *
     * @return int
     */
    public function getWordLength(): int {
        return $this->wordLength;
    }

    /**
     * Internal BitMap
     *
     * @var SplFixedArray<int> $internalBitMap
     */
    private SplFixedArray $internalBitMap;

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
     * Helper throwing Out of Bounds Exception when the $position doesn't fit the wordLength of this Structure
     *
     * @param integer $position
     * @return void
     * @throws OutOfBoundsException if $position is < 0 or >= word length
     */
    protected function assertInWordBounds(int $position) {
        if (($position < 0) || ($position >= $this->wordLength)) {
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
        if ($this->bitLength !== $bitMap->bitLength) {
            throw new OutOfBoundsException();
        }
    }

    /**
     * Returning the min. amount of Words needed to store the given bit length.
     *
     * @param int $bitLength
     * @return int
     */
    public static function BitLength2WordLength(int $bitLength): int {
        if (($rest = ($bitLength % self::BitPerWord)) !== 0) {
            $bitLength = $bitLength + (self::BitPerWord - $rest);
        }
        return intdiv($bitLength, self::BitPerWord);
    }

    /**
     * Return the position of the word which stores the given bit.
     *
     * @param int $bit
     * @return int
     */
    public static function BitToBufferIndex(int $bit): int {
        return intdiv($bit, self::BitPerWord);
    }

    /**
     * Return the position of the bit in it's respective "storage" word.
     *
     * @param int $bit
     * @return int
     */
    public static function BitToWordIndex(int $bit): int {
        return $bit % self::BitPerWord;
    }

    /**
     * BitMap constructor with given bit length
     *
     * @param int $bitLength
     */
    public function __construct(int $bitLength) {
        $this->bitLength = $bitLength;
        $this->wordLength = self::BitLength2WordLength($bitLength);
        $this->internalBitMap = new SplFixedArray($this->wordLength);
        for ($i = 0; $i < $this->wordLength; $i++) {
            $this->internalBitMap[$i] = 0;
        }
    }

    /**
     * Clone and make sure the internal Bitmap is duplicated correctly.
     */
    public function __clone() {
        $cloneSet = $this->internalBitMap;
        $this->internalBitMap = new SplFixedArray($this->wordLength);
        for ($i = 0; $i < $this->wordLength; $i++) {
            $this->internalBitMap[$i] = $cloneSet[$i];
        }
    }

    /**
     * Set bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 0 to 1, otherwise false
     */
    public function set(int $position): bool {
        $this->assertInBounds($position);
        $index = self::BitToBufferIndex($position);
        $shift = self::BitToWordIndex($position);
        $setBit = 1 << $shift;
        $block = $this->internalBitMap[$index];
        $bitClean = !(($block & $setBit) === $setBit);
        if ($bitClean) {
            $this->internalBitMap[$index] = $block | $setBit;
        }
        return $bitClean;
    }

    /**
     * Unset bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 1 to 0, otherwise false
     */
    public function unset(int $position): bool {
        $this->assertInBounds($position);
        $index = self::BitToBufferIndex($position);
        $shift = self::BitToWordIndex($position);
        $unsetBit = 1 << $shift;
        $block = $this->internalBitMap[$index];
        $bitSet = ($block & $unsetBit) === $unsetBit;
        if ($bitSet) {
            $this->internalBitMap[$index] = $block ^ $unsetBit;
        }
        return $bitSet;
    }

    /**
     * Check if bit at given position is set
     *
     * @param int $position
     * @return bool true if bit at given position is set, false if it's not set
     */
    public function test(int $position): bool {
        $this->assertInBounds($position);
        $index = self::BitToBufferIndex($position);
        $shift = self::BitToWordIndex($position);
        $testBit = 1 << $shift;
        return ($this->internalBitMap[$index] & $testBit) === $testBit;
    }

    /**
     * Execute given callable with signature function(int $block, int $position): int|false
     * for each word sized block of the internal bitmap.
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
        for ($i = 0; $i < $this->wordLength; $i++) {
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
     * Get bitmap block (Machine Word packed bits) at given position.
     *
     * @param int $position
     * @return int
     */
    public function getBlock(int $position): int {
        $this->assertInWordBounds($position);
        $result = $this->internalBitMap[$position];
        if ($position === $this->wordLength - 1) {
            $lastBlockUpperBit = $this->bitLength % self::BitPerWord;
            if ($lastBlockUpperBit !== 0) {
                $upperBit = 1 << ($lastBlockUpperBit - 1);
                $mask = ~((~($upperBit)) + 1) | $upperBit;
                $result = $result & $mask;
            }
        }
        return $result;
    }

    /**
     * Set bitmap block (Machine Word packed bits) at given position.
     *
     * @param int $position
     * @param int $block
     * @return bool true if there was an actual change, false otherwise
     */
    public function setBlock(int $position, int $block): bool {
        $this->assertInWordBounds($position);
        $before = $this->internalBitMap[$position];
        if ($position === $this->wordLength - 1) {
            $lastBlockUpperBit = $this->bitLength % self::BitPerWord;
            if ($lastBlockUpperBit !== 0) {
                $upperBit = 1 << ($lastBlockUpperBit - 1);
                $mask = ~((~($upperBit)) + 1) | $upperBit;
                $block = $block & $mask;
            }
        }
        if ($before !== $block) {
            $this->internalBitMap[$position] = $block;
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
        // f.e. bit 4 (0 index) = ~b00010000 -> b11101111 +1 -> b11110000
        $fromBlock = (~(1 << self::BitToWordIndex($from))) + 1;
        // f.e. bit 4 (0 index) = ~b00010000 -> b11101111 +1 -> ~b11110000 | b00010000 -> b00011111
        $toBit = 1 << self::BitToWordIndex($to);
        $toBlock = ~((~($toBit)) + 1) | $toBit;
        $startWord = self::BitToBufferIndex($from);
        $endWord = self::BitToBufferIndex($to);
        // case 2.: from and to are in the same block
        if ($startWord === $endWord) {
            $oldWord = $this->internalBitMap[$startWord];
            $update = $oldWord | ($fromBlock & $toBlock);
            if ($update !== $oldWord) {
                $this->internalBitMap[$startWord] = $update;
                return true;
            }
            return false;
        }
        // case 3.: from and to are in the different blocks where to = from+1
        $changed = false;
        $oldWord = $this->internalBitMap[$startWord];
        if (($oldWord | $fromBlock) !== $oldWord) {
            $this->internalBitMap[$startWord] = $oldWord | $fromBlock;
            $changed = true;
        }
        $oldWord = $this->internalBitMap[$endWord];
        if (($oldWord | $toBlock) !== $oldWord) {
            $this->internalBitMap[$endWord] = $oldWord | $toBlock;
            $changed = true;
        }
        // case 4.: from and to are in the different blocks where to = from+n with n > 1
        while (++$startWord < $endWord) {
            $changed = $changed || (~0 !== $this->internalBitMap[$startWord]);
            $this->internalBitMap[$startWord] = ~0;
        }
        return $changed;
    }

}
