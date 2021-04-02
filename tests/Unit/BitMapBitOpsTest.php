<?php

use makadev\BitSet\BitMap\MemoryBitMap;
use makadev\BitSet\BitMap\SPLArrayBitMap;
use makadev\BitSet\BitMap\StringBitMap;
use PHPUnit\Framework\TestCase;

class BitMapBitOpsTest extends TestCase {

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
        $bitMap = new $implementation(1);
        $bitMap->set(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->set(1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenSetNegative(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->set(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->set(-1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenUnsetOverLength(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->unset(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->unset(1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenUnsetNegative(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->unset(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->unset(-1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenTestOverLength(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->test(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->test(1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapOutOfBoundsWhenTestNegative(string $implementation): void {
        $bitMap = new $implementation(1);
        $bitMap->test(0);
        $this->expectException(OutOfBoundsException::class);
        $bitMap->test(-1);
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testBitMapLength(string $implementation): void {
        $bitMap = new $implementation(16);
        $this->assertEquals(16, $bitMap->getBitLength());
        $bitMap = new $implementation(432);
        $this->assertEquals(432, $bitMap->getBitLength());
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function testSetUnsetBitResult(string $implementation): void {
        $bitMap = new $implementation(1);
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
        $make_unset = function ($bits) {
            return [
                // size, position
                [1, 0],
                // Single Word, MSB/LSB
                [$bits, 0],
                [$bits, $bits - 1],
                // Multi Word, MSB/LSB per Word
                [$bits * 2, 0],
                [$bits * 2, $bits - 1],
                [$bits * 2, $bits],
                [$bits * 2, $bits * 2 - 1]
            ];
        };
        $unset = array_merge($make_unset(8), $make_unset(16), $make_unset(32), $make_unset(64));
        $implementations = $this->implementationProvider();
        $provided = [];
        foreach ($implementations as $implparams) {
            foreach ($unset as $unsetparams) {
                $provided[] = array_merge($implparams, $unsetparams);
            }
        }
        return $provided;
    }

    /**
     * @dataProvider setUnsetTestProvider
     * @param string $implementation
     * @param int $size
     * @param int $position
     */
    public function testSetUnsetTestOperation(string $implementation, int $size, int $position): void {
        $bitMap = new $implementation($size);
        $this->assertFalse($bitMap->test($position));
        $bitMap->set($position);
        $this->assertTrue($bitMap->test($position));
        $bitMap->unset($position);
        $this->assertFalse($bitMap->test($position));
    }

    /**
     * @dataProvider implementationProvider
     * @param string $implementation
     */
    public function setUnsetTestTogether(string $implementation): void {
        $bits = [
            0,
            $implementation::BitPerWord - 1,
            $implementation::BitPerWord,
            $implementation::BitPerWord * 2 - 1,
            85,
            119,
            149,
        ];
        $bitMap = new $implementation(150);
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