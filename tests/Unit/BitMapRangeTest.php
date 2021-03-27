<?php

use makadev\BitSet\BitMap\SPLArrayBitMap;
use makadev\BitSet\BitMap\StringBitMap;
use PHPUnit\Framework\TestCase;

class BitMapRangeTest extends TestCase {

    public function implementationProvider(): array {
        return [
            [makadev\BitSet\BitMap::class],
            [SPLArrayBitMap::class],
            [StringBitMap::class],
        ];
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenSetOverLength(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(1, 0);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenSetOverLength2(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(0, 1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenSetNegative(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(-1, 0);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenSetNegative2(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->setRange(0, 0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->setRange(0, -1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapInputOrderNOP(string $implementation): void {
        $bitMap = new $implementation(10);
        $res = $bitMap->setRange(8, 2);
        $this->assertFalse($res);
        $this->assertEquals(0, $bitMap->getBlock(0));
    }

    /**
     * Data Provider for range test
     *
     * @return array<array<int>>
     */
    public function rangeSetProvider(): array {
        $make_rangeset = function ($bits) {
            return [
                // size, range start, range end
                [1, 0, 0],
                // Single Word
                [$bits, intdiv($bits, 2), intdiv($bits, 2)],
                [$bits, 0, intdiv($bits, 2)],
                [$bits, intdiv($bits, 2), $bits - 1],
                [$bits, 0, $bits - 1],
                // Multi Word, MSB/LSB  Word
                [$bits * 2, 0, $bits - 1],
                [$bits * 2, 0, $bits],
                [$bits * 2, $bits - 1, $bits],
                [$bits * 2, $bits - 1, ($bits * 2) - 1],
                [$bits * 2, $bits, ($bits * 2) - 1],
                //
                [$bits * 3, 0, $bits - 1],
                [$bits * 3, 0, $bits],
                [$bits * 3, 0, $bits * 2 - 1],
                [$bits * 3, 0, $bits * 2],
                [$bits * 3, $bits - 1, $bits],
                [$bits * 3, $bits * 2 - 1, $bits * 2],
                [$bits * 3, $bits - 1, ($bits * 3) - 1],
                [$bits * 3, $bits, ($bits * 3) - 1],
                [$bits * 3, $bits * 2 - 1, ($bits * 3) - 1],
                [$bits * 3, $bits * 2, ($bits * 3) - 1]
            ];
        };
        $rangeset = array_merge($make_rangeset(8), $make_rangeset(16), $make_rangeset(32), $make_rangeset(64));
        $implementations = $this->implementationProvider();
        $provided = [];
        foreach ($implementations as $implparams) {
            foreach ($rangeset as $rangesetparams) {
                $provided[] = array_merge($implparams, $rangesetparams);
            }
        }
        return $provided;
    }

    /**
     * @dataProvider rangeSetProvider
     * @param string $implementation
     * @param int $size
     * @param int $start
     * @param int $end
     */
    public function testSetRangeOperation(string $implementation, int $size, int $start, int $end): void {
        $bitMap = new $implementation($size);
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