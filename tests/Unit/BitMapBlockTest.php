<?php

use makadev\BitSet\BitMap;
use PHPUnit\Framework\TestCase;

class BitMapBlockTest extends TestCase {

    public function testBitMapOutOfBoundsWhenSetOverLength(): void {
        $bitMap = new BitMap(PHP_INT_SIZE * 8);
        $bitMap->setBlock(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setBlock(1, 0);
    }

    public function testBitMapOutOfBoundsWhenSetNegative(): void {
        $bitMap = new BitMap(PHP_INT_SIZE * 8);
        $bitMap->setBlock(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setBlock(-1, 0);
    }

    public function testBitMapOutOfBoundsWhenGetOverLength(): void {
        $bitMap = new BitMap(PHP_INT_SIZE * 8);
        $bitMap->getBlock(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->getBlock(1);
    }

    public function testBitMapOutOfBoundsWhenGetNegative(): void {
        $bitMap = new BitMap(PHP_INT_SIZE * 8);
        $bitMap->getBlock(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->getBlock(-1);
    }

    public function testEmptyBitMapVisitor(): void {
        $bitMap = new BitMap((int)(BitMap::BitPerWord * 2.5));
        $bitMap->eachBlock(function (int $block, int $index) {
            $this->assertEquals(0, $block);
            return true;
        });
    }

    public function testComplementBitMapVisitor(): void {
        $bitMap = new BitMap(130);
        // complement
        $bitMap->eachBlock(function(int $block, int $index) {return ~$block;});
        // check complement 130bits = 2x64bit or 4x32bit set and 2 bits extra resulting in bitmask (1 shl 1) | 1
        $bitMap->eachBlock(function (int $block, int $index) use ($bitMap) {
            if ($index < intdiv($bitMap->getBitLength(), BitMap::BitPerWord)) {
                $this->assertEquals(~0, $block);
            } else {
                $this->assertEquals((1 << 1) | 1, $block);
            }
            return true;
        });
        // early exit returning false
        $bitMap->eachBlock(function (int $block, int $index) {
            $this->assertEquals(~0, $block);
            return false;
        });
        // expect no change when setting all bits in the lowest block
        $bitMap->setBlock(0, ~0);
        // expect no change setting all bits in the highest block
        $this->assertFalse($bitMap->setBlock(intdiv(128, PHP_INT_SIZE * 8), ~0));
        $this->assertFalse($bitMap->setBlock(intdiv(128, PHP_INT_SIZE * 8), (1 << 1) | 1));
        // zero
        $bitMap->eachBlock(function(int $block, int $index) {return 0;});
    }
}