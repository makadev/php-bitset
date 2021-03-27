<?php


namespace makadev\BitSet\Contract;

interface BitSet extends BitMap {

    /**
     * Check if bitset is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Check that this bitset is disjoint with the given one, meaning they have no common elements.
     *
     * @param BitMap $other
     * @return bool
     */
    public function isDisjoint(BitMap $other): bool;

    /**
     * Check that this contains all elements of $other.
     *
     * @param BitMap $other
     * @return bool
     */
    public function contains(BitMap $other): bool;

    /**
     * Check that this set has the same elements as $other.
     *
     * @param BitMap $other
     * @return bool
     */
    public function equals(BitMap $other): bool;

    /**
     * Calculate the set union C=A+B of the two sets
     *
     * @param BitSet $other
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function union(BitSet $other, bool $mutate = false): BitSet;

    /**
     * Calculate the set intersection C=A&B  of the two sets
     *
     * @param BitSet $other
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function intersect(BitSet $other, bool $mutate = false): BitSet;

    /**
     * Calculate the set minus C=A-B for the 2 sets
     *
     * @param BitSet $other
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function subtract(BitSet $other, bool $mutate = false): BitSet;

    /**
     * Calculate the complement C=U-A (given U is the full set U=[0...bitlength-1])
     *
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function complement(bool $mutate = false): BitSet;
}