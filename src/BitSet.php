<?php


namespace makadev\BitSet;

/**
 * Class for dense integer sets and operations.
 *
 * @package makadev\BitSet\BitSet
 */
class BitSet extends BitMap {

    /**
     * Check if bitset is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool {
        $empty = true;
        $this->eachBlock(function (int $block, int $index) use (&$empty) {
            $empty = $block === 0;
            return $empty;
        });
        return $empty;
    }

    /**
     * Check that this bitset is disjoint with the given one, meaning they have no common elements.
     *
     * @param BitMap $other
     * @return bool
     */
    public function isDisjoint(BitMap $other): bool {
        $this->assertBitLengthMatch($other);
        $result = true;
        $this->eachBlock(function (int $block, int $index) use (&$result, $other) {
            $result = ($block & $other->getBlock($index)) === 0;
            return $result;
        });
        return $result;
    }

    /**
     * Check that this contains all elements of $other.
     *
     * @param BitMap $other
     * @return bool
     */
    public function contains(BitMap $other): bool {
        $this->assertBitLengthMatch($other);
        $contains = true;
        $this->eachBlock(function (int $rightBlock, int $index) use (&$contains, $other) {
            $leftBlock = $other->getBlock($index);
            $contains = ($leftBlock ^ ($leftBlock & $rightBlock)) === 0;
            return $contains;
        });
        return $contains;
    }

    /**
     * Check that this set has the same elements as $other.
     *
     * @param BitMap $other
     * @return bool
     */
    public function equals(BitMap $other): bool {
        $this->assertBitLengthMatch($other);
        $equals = true;
        $this->eachBlock(function (int $block, int $index) use (&$equals, $other) {
            $equals = $block === $other->getBlock($index);
            return $equals;
        });
        return $equals;
    }

    /**
     * Calculate the set union C=A+B of the two sets
     *
     * @param BitSet $other
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function union(BitSet $other, bool $mutate = false): BitSet {
        $this->assertBitLengthMatch($other);
        if ($mutate) {
            $result = $this;
        } else {
            $result = clone $this;
        }
        $result->eachBlock(function (int $block, int $index) use ($other) {
            return $block | $other->getBlock($index);
        });
        return $result;
    }

    /**
     * Calculate the set intersection C=A&B  of the two sets
     *
     * @param BitSet $other
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function intersect(BitSet $other, bool $mutate = false): BitSet {
        $this->assertBitLengthMatch($other);
        if ($mutate) {
            $result = $this;
        } else {
            $result = clone $this;
        }
        $result->eachBlock(function (int $block, int $index) use ($other) {
            return ($block & $other->getBlock($index));
        });
        return $result;
    }

    /**
     * Calculate the substraction (set minus) C=A-B for the 2 sets
     *
     * @param BitSet $other
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function subtract(BitSet $other, bool $mutate = false): BitSet {
        $this->assertBitLengthMatch($other);
        if ($mutate) {
            $result = $this;
        } else {
            $result = clone $this;
        }
        $result->eachBlock(function (int $block, int $index) use ($other) {
            return ($block ^ ($block & $other->getBlock($index)));
        });
        return $result;
    }

    /**
     * Calculate the complement C=U-A (given U is the full set U=[0...bitlength-1])
     *
     * @param bool $mutate if set, $this will be modified and returned, otherwise it will create a new set and return it
     * @return BitSet
     */
    public function complement(bool $mutate = false): BitSet {
        if ($mutate) {
            $result = $this;
        } else {
            $result = clone $this;
        }
        $result->eachBlock(function (int $block, int $index) {
            return ~$block;
        });
        return $result;
    }
}