<?php


namespace makadev\BitSet\Contract;

use OutOfBoundsException;

abstract class BitMap {
    /**
     * Nr. Bits per Machine Word
     */
    public const BitPerWord = PHP_INT_SIZE * 8;

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
     * Length in Words (for internal Array of Words)
     *
     * @var int $wordLength
     */
    protected $wordLength;

    /**
     * Get the Length of BitMap in Words
     *
     * @return int
     */
    public function getWordLength(): int {
        return $this->wordLength;
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
    public abstract function __construct(int $bitLength);

    /**
     * Clone and make sure the internal Bitmap is duplicated correctly.
     */
    public abstract function __clone();

    /**
     * Set bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 0 to 1, otherwise false
     */
    public abstract function set(int $position): bool;

    /**
     * Unset bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 1 to 0, otherwise false
     */
    public abstract function unset(int $position): bool;

    /**
     * Check if bit at given position is set
     *
     * @param int $position
     * @return bool true if bit at given position is set, false if it's not set
     */
    public abstract function test(int $position): bool;

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
    public abstract function eachBlock(callable $f): bool;

    /**
     * Get bitmap block (Machine Word packed bits) at given position.
     *
     * @param int $position
     * @return int
     */
    public abstract function getBlock(int $position): int;

    /**
     * Set bitmap block (Machine Word packed bits) at given position.
     *
     * @param int $position
     * @param int $block
     * @return bool true if there was an actual change, false otherwise
     */
    public abstract function setBlock(int $position, int $block): bool;

    /**
     * Set all bits in a certain range
     *
     * @param int $from
     * @param int $to
     * @return bool true if at least one bit was changed from 0 to 1, otherwise false
     */
    public abstract function setRange(int $from, int $to): bool;

}
