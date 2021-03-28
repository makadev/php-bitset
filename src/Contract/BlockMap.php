<?php


namespace makadev\BitSet\Contract;

interface BlockMap {

    /**
     * Returning the Block Size in Bytes
     *
     * @return int
     */
    public static function BytesPerBlock(): int;

    /**
     * Get the length of the BlockMap in blocks
     *
     * @return int
     */
    public function getBlockLength(): int;

    /**
     * Read the Block at given position.
     *
     * @param int $position
     * @return int block at given position
     */
    public function readBlockMap(int $position): int;

    /**
     * Write Word at given position.
     *
     * @param int $position
     * @param int $block Block to be written
     */
    public function writeBlockMap(int $position, int $block): void;
}
