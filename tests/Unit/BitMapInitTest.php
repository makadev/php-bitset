<?php

use makadev\BitSet\BitMap\SPLArrayBitMap;
use makadev\BitSet\BitMap\StringBitMap;
use PHPUnit\Framework\TestCase;

class BitMapInitTest extends TestCase {

    public function implementationProvider(): array {
        return [
            [makadev\BitSet\BitMap::class],
            [SPLArrayBitMap::class],
            [StringBitMap::class],
        ];
    }

    /**
     * New set is empty
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testNewBitMapIsEmpty(string $implementation): void {
        $bitMap = new $implementation((int)(64 * 2.5));
        for ($i = 0; $i < $bitMap->getBitLength(); $i++) {
            $this->assertFalse($bitMap->test($i));
        }
        $this->assertEquals((int)(64 * 2.5), $bitMap->getBitLength());
    }

    /**
     * Cloned empty set is empty
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testNewClonedSet(string $implementation): void {
        $bitMap = new $implementation((int)(64 * 2.5));
        $bitMapClone = clone $bitMap;
        for ($i = 0; $i < $bitMapClone->getBitLength(); $i++) {
            $this->assertFalse($bitMapClone->test($i));
        }
        $this->assertEquals((int)(64 * 2.5), $bitMap->getBitLength());
    }
}