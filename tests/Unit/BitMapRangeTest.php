<?php

use makadev\BitSet\BitMap;
use PHPUnit\Framework\TestCase;

class BitMapRangeTest extends TestCase {

    public function testBitMapOutOfBoundsWhenSetOverLength(): void {
        $bitMap = new BitMap(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(1, 0);
    }

    public function testBitMapOutOfBoundsWhenSetOverLength2(): void {
        $bitMap = new BitMap(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(0, 1);
    }

    public function testBitMapOutOfBoundsWhenSetNegative(): void {
        $bitMap = new BitMap(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(-1, 0);
    }

    public function testBitMapOutOfBoundsWhenSetNegative2(): void {
        $bitMap = new BitMap(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(0, -1);
    }

    public function testBitMapInputOrderNOP(): void {
        $bitMap = new BitMap(10);
        $res = $bitMap->setRange(8, 2);
        $this->assertFalse($res);
        $this->assertEquals(0, $bitMap->getBlock(0));
    }

    /**
     * Data Provider for testRangeSet
     *
     * @return array<array<int>>
     */
    public function rangeSetProvider(): array {
        return [
            // size, range start, range end
            [1, 0, 0],
            // Single Word
            [BitMap::BitPerWord, 16, 16],
            [BitMap::BitPerWord, 0, 16],
            [BitMap::BitPerWord, 16, BitMap::BitPerWord - 1],
            [BitMap::BitPerWord, 0, BitMap::BitPerWord - 1],
            // Multi Word, MSB/LSB  Word
            [BitMap::BitPerWord * 2, 0, BitMap::BitPerWord - 1],
            [BitMap::BitPerWord * 2, 0, BitMap::BitPerWord],
            [BitMap::BitPerWord * 2, BitMap::BitPerWord - 1, BitMap::BitPerWord],
            [BitMap::BitPerWord * 2, BitMap::BitPerWord - 1, (BitMap::BitPerWord * 2) - 1],
            [BitMap::BitPerWord * 2, BitMap::BitPerWord, (BitMap::BitPerWord * 2) - 1],
            //
            [BitMap::BitPerWord * 3, 0, BitMap::BitPerWord - 1],
            [BitMap::BitPerWord * 3, 0, BitMap::BitPerWord],
            [BitMap::BitPerWord * 3, 0, BitMap::BitPerWord * 2 - 1],
            [BitMap::BitPerWord * 3, 0, BitMap::BitPerWord * 2],
            [BitMap::BitPerWord * 3, BitMap::BitPerWord - 1, BitMap::BitPerWord],
            [BitMap::BitPerWord * 3, BitMap::BitPerWord * 2 - 1, BitMap::BitPerWord * 2],
            [BitMap::BitPerWord * 3, BitMap::BitPerWord - 1, (BitMap::BitPerWord * 3) - 1],
            [BitMap::BitPerWord * 3, BitMap::BitPerWord, (BitMap::BitPerWord * 3) - 1],
            [BitMap::BitPerWord * 3, BitMap::BitPerWord * 2 - 1, (BitMap::BitPerWord * 3) - 1],
            [BitMap::BitPerWord * 3, BitMap::BitPerWord * 2, (BitMap::BitPerWord * 3) - 1]
        ];
    }

    /**
     * @dataProvider rangeSetProvider
     * @param int $size
     * @param int $start
     * @param int $end
     */
    public function testSetRangeOperation(int $size, int $start, int $end): void {
        $bitMap = new BitMap($size);
        $res = $bitMap->setRange($start, $end);
        $this->assertTrue($res);
        for ($i = 0; $i < $bitMap->getBitLength(); $i++) {
            $this->assertEquals($i >= $start && $i <= $end, $bitMap->test($i));
        }
        $res = $bitMap->setRange($start, $end);
        $this->assertFalse($res);
        for ($i = 0; $i < $bitMap->getBitLength(); $i++) {
            $this->assertEquals($i >= $start && $i <= $end, $bitMap->test($i));
        }
    }
}