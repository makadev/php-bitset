<?php

namespace makadev\BitSet\BitSet;

use makadev\BitSet\BlockMap\StringBlockMap;

class StringBitSet extends BlockBitSet {

    public function __construct(int $bitLength) {
        $blocks = intdiv($bitLength, StringBlockMap::BytesPerBlock() * 8);
        if (($blocks * StringBlockMap::BytesPerBlock() * 8) < $bitLength) {
            $blocks++;
        }
        parent::__construct($bitLength, new StringBlockMap($blocks));
    }
}