<?php

use makadev\BitSet\BitMap;
use PHPUnit\Framework\TestCase;

class BitMapBitOpsTest extends TestCase {

    public function testBitMapOutOfBoundsWhenSetOverLength(): void {
        $bitMap = new BitMap(1);
        $bitMap->set(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->set(1);
    }

    public function testBitMapOutOfBoundsWhenSetNegative(): void {
        $bitMap = new BitMap(1);
        $bitMap->set(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->set(-1);
    }

    public function testBitMapOutOfBoundsWhenUnsetOverLength(): void {
        $bitMap = new BitMap(1);
        $bitMap->unset(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->unset(1);
    }

    public function testBitMapOutOfBoundsWhenUnsetNegative(): void {
        $bitMap = new BitMap(1);
        $bitMap->unset(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->unset(-1);
    }

    public function testBitMapOutOfBoundsWhenTestOverLength(): void {
        $bitMap = new BitMap(1);
        $bitMap->test(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->test(1);
    }

    public function testBitMapOutOfBoundsWhenTestNegative(): void {
        $bitMap = new BitMap(1);
        $bitMap->test(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->test(-1);
    }

    public function testBitMapLength(): void {
        $bitMap = new BitMap(16);
        $this->assertEquals(16, $bitMap->getBitLength());
        $bitMap = new BitMap(432);
        $this->assertEquals(432, $bitMap->getBitLength());
    }

    public function testSetUnsetBitResult(): void {
        $bitMap = new BitMap(1);
        $this->assertTrue($bitMap->set(0));
        $this->assertFalse($bitMap->set(0));
        $this->assertTrue($bitMap->test(0));
        $this->assertTrue($bitMap->unset(0));
        $this->assertFalse($bitMap->unset(0));
        $this->assertFalse($bitMap->test(0));
        $this->assertTrue($bitMap->set(0));
        $this->assertTrue($bitMap->unset(0));
    }

    /**
     * Data Provider for setUnsetTestProvider / setUnsetTestTogether
     *
     * @return array<array<int>>
     */
    public function setUnsetTestProvider(): array {
        return [
            // size, position
            [1, 0],
            // Single Word, MSB/LSB
            [BitMap::BitPerWord, 0],
            [BitMap::BitPerWord, BitMap::BitPerWord - 1],
            // Multi Word, MSB/LSB per Word
            [BitMap::BitPerWord * 2, 0],
            [BitMap::BitPerWord * 2, BitMap::BitPerWord - 1],
            [BitMap::BitPerWord * 2, BitMap::BitPerWord],
            [BitMap::BitPerWord * 2, BitMap::BitPerWord * 2 - 1]
        ];
    }

    /**
     * @dataProvider setUnsetTestProvider
     * @param int $size
     * @param int $position
     */
    public function testSetUnsetTestOperation(int $size, int $position): void {
        $bitMap = new BitMap($size);
        $this->assertFalse($bitMap->test($position));
        $bitMap->set($position);
        $this->assertTrue($bitMap->test($position));
        $bitMap->unset($position);
        $this->assertFalse($bitMap->test($position));
    }

    public function setUnsetTestTogether(): void {
        $bits = [
            0,
            BitMap::BitPerWord - 1,
            BitMap::BitPerWord,
            BitMap::BitPerWord * 2 - 1,
            85,
            119,
            149,
        ];
        $bitMap = new BitMap(150);
        // single bit mod
        foreach ($bits as $position) {
            $this->assertFalse($bitMap->test($position));
            $bitMap->set($position);
            $this->assertTrue($bitMap->test($position));
            $bitMap->unset($position);
            $this->assertFalse($bitMap->test($position));
        }
        // assert empty
        for ($i = 0; $i < $bitMap->getBitLength(); $i++) {
            $this->assertFalse($bitMap->test($i));
        }
        // multi bit mod
        foreach ($bits as $position) {
            $bitMap->set($position);
            $this->assertTrue($bitMap->test($position));
        }
        foreach ($bits as $position) {
            $bitMap->unset($position);
            $this->assertFalse($bitMap->test($position));
        }
        // assert empty
        for ($i = 0; $i < $bitMap->getBitLength(); $i++) {
            $this->assertFalse($bitMap->test($i));
        }
    }
}