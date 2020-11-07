<?php

use makadev\BitSet\BitMap;
use PHPUnit\Framework\TestCase;

class BitMapInitTest extends TestCase {

    /**
     * New set is empty
     *
     */
    public function testNewBitMapIsEmpty(): void {
        $bitMap = new BitMap((int)(BitMap::BitPerWord * 2.5));
        for ($i = 0; $i < $bitMap->getBitLength(); $i++) {
            $this->assertFalse($bitMap->test($i));
        }
        $this->assertEquals((int)(BitMap::BitPerWord * 2.5), $bitMap->getBitLength());
        $this->assertEquals(3, $bitMap->getWordLength());
    }

    /**
     * Cloned empty set is empty
     *
     */
    public function testNewClonedSet(): void {
        $bitMap = new BitMap((int)(BitMap::BitPerWord * 2.5));
        $bitMapClone = clone $bitMap;
        for ($i = 0; $i < $bitMapClone->getBitLength(); $i++) {
            $this->assertFalse($bitMapClone->test($i));
        }
        $this->assertEquals((int)(BitMap::BitPerWord * 2.5), $bitMap->getBitLength());
        $this->assertEquals(3, $bitMap->getWordLength());
    }
}