<?php

namespace makadev\BitSet\BitSet;

use makadev\BitSet\BlockMap\MemoryBlockMap;

class MemoryBitSet extends BlockBitSet {

    public function __construct(int $bitLength) {
        $blocks = intdiv($bitLength, MemoryBlockMap::BytesPerBlock() * 8);
        if (($blocks * MemoryBlockMap::BytesPerBlock() * 8) < $bitLength) {
            $blocks++;
        }
        parent::__construct($bitLength, new MemoryBlockMap($blocks));
    }
}