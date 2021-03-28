<?php

namespace makadev\BitSet\BitMap;

use makadev\BitSet\BlockMap\StringBlockMap;

class StringBitMap extends BlockBitMap {

    public function __construct(int $bitLength) {
        $blocks = intdiv($bitLength, StringBlockMap::BytesPerBlock() * 8);
        if (($blocks * StringBlockMap::BytesPerBlock() * 8) < $bitLength) {
            $blocks++;
        }
        parent::__construct($bitLength, new StringBlockMap($blocks));
    }
}