<?php

namespace makadev\BitSet\BitSet;

use makadev\BitSet\BlockMap\SPLArrayBlockMap;

class SPLArrayBitSet extends BlockBitSet {

    public function __construct(int $bitLength) {
        $blocks = intdiv($bitLength, SPLArrayBlockMap::BytesPerBlock() * 8);
        if (($blocks * SPLArrayBlockMap::BytesPerBlock() * 8) < $bitLength) {
            $blocks++;
        }
        parent::__construct($bitLength, new SPLArrayBlockMap($blocks));
    }
}