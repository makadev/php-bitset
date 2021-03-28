<?php


namespace makadev\BitSet\Contract;

use OutOfBoundsException;

interface BitMap {

    /**
     * Get the Length of this BitMap in Bits
     *
     * @return int
     */
    public function getBitLength(): int;

    /**
     * Set bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 0 to 1, otherwise false
     * @throws OutOfBoundsException if position is invalid
     */
    public function set(int $position): bool;

    /**
     * Unset bit at given position
     *
     * @param int $position
     * @return bool true if bit was changed from 1 to 0, otherwise false
     * @throws OutOfBoundsException if position is invalid
     */
    public function unset(int $position): bool;

    /**
     * Check if bit at given position is set
     *
     * @param int $position
     * @return bool true if bit at given position is set, false if it's not set
     * @throws OutOfBoundsException if position is invalid
     */
    public function test(int $position): bool;

    /**
     * Return the amount of bits for a block (especially for the block functions)
     *
     * @return int
     */
    public function getBitsPerBlock(): int;

    /**
     * Execute given callable with signature function(int $block, int $position): int|false
     * for each block of the internal bitmap.
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
    public function eachBlock(callable $f): bool;

    /**
     * Get bitmap block (Machine Word packed bits) at given position.
     *
     * @param int $position
     * @return int
     * @throws OutOfBoundsException if position is invalid
     */
    public function getBlock(int $position): int;

    /**
     * Set bitmap block (Machine Word packed bits) at given position.
     *
     * @param int $position
     * @param int $block
     * @return bool true if there was an actual change, false otherwise
     * @throws OutOfBoundsException if position is invalid
     */
    public function setBlock(int $position, int $block): bool;

    /**
     * Set all bits in a certain range
     *
     * @param int $from
     * @param int $to
     * @return bool true if at least one bit was changed from 0 to 1, otherwise false
     * @throws OutOfBoundsException if from or to is invalid
     */
    public function setRange(int $from, int $to): bool;

}
