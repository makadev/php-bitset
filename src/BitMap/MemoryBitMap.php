<?php

namespace makadev\BitSet\BitMap;

use makadev\BitSet\BlockMap\MemoryBlockMap;

class MemoryBitMap extends BlockBitMap {

    public function __construct(int $bitLength) {
        $blocks = intdiv($bitLength, MemoryBlockMap::BytesPerBlock() * 8);
        if (($blocks * MemoryBlockMap::BytesPerBlock() * 8) < $bitLength) {
            $blocks++;
        }
        parent::__construct($bitLength, new MemoryBlockMap($blocks));
    }
}