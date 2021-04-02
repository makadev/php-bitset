<?php

use makadev\BitSet\BitMap\MemoryBitMap;
use makadev\BitSet\BitMap\SPLArrayBitMap;
use makadev\BitSet\BitMap\StringBitMap;
use PHPUnit\Framework\TestCase;

class BitMapBlockTest extends TestCase {

    public function implementationProvider(): array {
        return [
            [makadev\BitSet\BitMap::class],
            [SPLArrayBitMap::class],
            [StringBitMap::class],
            [MemoryBitMap::class],
        ];
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenSetOverLength(string $implementation): void {
        $bitMap = new $implementation(64);
        $bitMap->setBlock(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setBlock($bitMap->getBlockLength(), 0);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenSetNegative(string $implementation): void {
        $bitMap = new $implementation(64);
        $bitMap->setBlock(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setBlock(-1, 0);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenGetOverLength(string $implementation): void {
        $bitMap = new $implementation(64);
        $bitMap->getBlock(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->getBlock($bitMap->getBlockLength());
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenGetNegative(string $implementation): void {
        $bitMap = new $implementation(64);
        $bitMap->getBlock(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->getBlock(-1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testEmptyBitMapVisitor(string $implementation): void {
        $bitMap = new $implementation(68);
        $bitMap->eachBlock(function (int $block, int $index) {
            $this->assertEquals(0, $block);
            return true;
        });
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testComplementBitMapVisitor(string $implementation): void {
        $bitMap = new $implementation(130);
        // complement
        $bitMap->eachBlock(function (int $block, int $index) {
            return ~$block;
        });
        // check complement 130bits = 2x64bit or 4x32bit or 16x8bit set
        // and 2 bits extra resulting in bitmask (1 shl 1) | 1
        $bitMap->eachBlock(function (int $block, int $index) use ($bitMap) {
            if ($index < intdiv($bitMap->getBitLength(), $bitMap->getBitsPerBlock())) {
                if ($bitMap->getBitsPerBlock() === 64 && (PHP_INT_SIZE === 8)) {
                    $this->assertEquals(~0, $block);
                } elseif ($bitMap->getBitsPerBlock() === 32 && (PHP_INT_SIZE === 4)) {
                    $this->assertEquals(~0, $block);
                } elseif ($bitMap->getBitsPerBlock() === 8) {
                    $this->assertEquals(0xFF, $block);
                } else {
                    $this->fail("Test doesn't support given combination of int size and BitMap Block size");
                }
            } else {
                $this->assertEquals((1 << 1) | 1, $block);
            }
            return true;
        });
        // early exit returning false
        $bitMap->eachBlock(function (int $block, int $index) {
            $this->assertEquals(0, $index);
            return false;
        });
        // expect no change when setting all bits in the lowest block
        $bitMap->setBlock(0, ~0);
        // expect no change setting all bits in the highest block
        $this->assertFalse($bitMap->setBlock(intdiv(128, $bitMap->getBitsPerBlock()), ~0));
        $this->assertFalse($bitMap->setBlock(intdiv(128, $bitMap->getBitsPerBlock()), (1 << 1) | 1));
        // zero
        $bitMap->eachBlock(function (int $block, int $index) {
            return 0;
        });
    }
}