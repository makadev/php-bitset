<?php

use makadev\BitSet\BitSet;
use PHPUnit\Framework\TestCase;

class BitSetTest extends TestCase {

    public function testBitMapLengthMatch(): void {
        $bitSet = new BitSet(1);
        $bitSet2 = new BitSet(2);
        $this->expectException(OutOfBoundsException::class);
        $bitSet->union($bitSet2);
    }

    /**
     * Bit set followed by a bit unset
     *
     */
    public function testHeavyIsEmpty(): void {
        $alpha = new BitSet(256);
        $alpha->set(1);
        $this->assertFalse($alpha->isEmpty());
        $alpha->unset(1);
        $this->assertTrue($alpha->isEmpty());
        $alpha->set(1);
        $alpha->set(255);
        $alpha->unset(1);
        $this->assertFalse($alpha->isEmpty());
    }

    /**
     * Test isDisjoint operation
     *
     */
    public function testIsDisjoint(): void {
        // trivial case: 2 empty sets are disjoint
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);
        $this->assertTrue($alpha1->isDisjoint($alpha2));

        // trivial case: also with one empty set
        $alpha1->set(5);
        $this->assertTrue($alpha1->isDisjoint($alpha2));

        // not disjoint
        // $alpha1->set(5); <- set above
        $alpha2->set(5);
        $this->assertFalse($alpha1->isDisjoint($alpha2));

        // disjoint with nonempty sets
        // $alpha1->set(5); <- set above
        // $alpha2->set(5); <- set above
        $alpha2->unset(5);
        $alpha2->set(6);
        $alpha2->set(255);
        $this->assertTrue($alpha1->isDisjoint($alpha2));
    }

    /**
     * Test contains operation
     */
    public function testContains(): void {
        // trivial case: empty set containment
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);
        $this->assertTrue($alpha1->contains($alpha2));

        // containment both sides (equality)
        $alpha1->set(5);
        $alpha2->set(5);
        $this->assertTrue($alpha1->contains($alpha2));
        $this->assertTrue($alpha2->contains($alpha1));

        // containment one side
        // $alpha1->set(5); <- set above
        // $alpha2->set(5); <- set above
        $alpha2->set(255);
        $this->assertFalse($alpha1->contains($alpha2));
        $this->assertTrue($alpha2->contains($alpha1));
    }

    /**
     * Test union operation
     *
     */
    public function testUnion(): void {
        // trivial case: 2 empty set union will just set empty flag on new set
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);
        $union = $alpha1->union($alpha2);
        $this->assertTrue($union->isEmpty());

        // union contains bits from both sets
        $alpha1->set(13);
        $alpha2->set(13);
        $alpha2->set(233);
        $union = $alpha1->union($alpha2);
        $this->assertFalse($union->isEmpty());
        $this->assertTrue($union->test(13));
        $this->assertTrue($union->test(233));

        // but not from other bits
        $union->unset(13);
        $union->unset(233);
        $this->assertTrue($union->isEmpty());
    }

    /**
     * Test Equals Check
     *
     */
    public function testEquals(): void {
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);
        $this->assertTrue($alpha1->equals($alpha2));

        $alpha1->set(13);
        $alpha2->set(13);
        $this->assertTrue($alpha1->equals($alpha2));

        $alpha1->set(23);
        $this->assertFalse($alpha1->equals($alpha2));
    }

    /**
     * Test mutating union operation returns left operand
     *
     */
    public function testMutatingUnion(): void {
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);
        $union = $alpha1->union($alpha2, true);
        $this->assertTrue($union->isEmpty());
        $this->assertSame($alpha1, $union);
    }

    /**
     * Test subtract operation
     *
     */
    public function testSubtract(): void {
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);

        // trivial case: subtract empty sets will simply set $empty and exit
        $empty = $alpha1->subtract($alpha2);
        $this->assertTrue($empty->isEmpty());

        // subtract resulting in empty set: check empty state
        $alpha1->set(5);
        $alpha2->set(5);
        $empty = $alpha1->subtract($alpha2);
        $this->assertTrue($empty->isEmpty());

        // subtract resulting in nonempty set: check leftover bits and empty state
        //$alpha1->set(5); <- already set above
        //$alpha2->set(5); <- already set above
        $alpha1->set(4);
        $alpha1->set(255);
        $alpha2->set(6);
        $set = $alpha1->subtract($alpha2);
        $this->assertTrue($set->test(4));
        $this->assertFalse($set->test(5));
        $this->assertFalse($set->test(6));
        $this->assertTrue($set->test(255));
        $this->assertFalse($set->isEmpty());

        // but not containing other bits
        $set->unset(4);
        $set->unset(255);
        $this->assertTrue($set->isEmpty());
    }

    /**
     * Test mutating subtract operation returns left operand
     *
     */
    public function testMutatingSubtract(): void {
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);
        $empty = $alpha1->subtract($alpha2, true);
        $this->assertTrue($empty->isEmpty());
        $this->assertSame($alpha1, $empty);
    }

    /**
     * Test intersect operation
     *
     */
    public function testIntersect(): void {
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);

        // trivial case: intersect empty sets will simply set $empty and exit
        $empty = $alpha1->intersect($alpha2);
        $this->assertTrue($empty->isEmpty());

        // intersect resulting in empty set: check empty state
        $alpha1->set(0);
        $alpha2->set(255);
        $empty = $alpha1->intersect($alpha2);
        $this->assertTrue($empty->isEmpty());

        // intersect resulting in nonempty set: check leftover bits and empty state
        // $alpha1->set(0); <- already set above
        // $alpha2->set(255); <- already set above
        $alpha1->set(5);
        $alpha2->set(5);
        $set = $alpha1->intersect($alpha2);
        $this->assertFalse($set->test(0));
        $this->assertTrue($set->test(5));
        $this->assertFalse($set->test(255));
        $this->assertFalse($set->isEmpty());

        // but not containing other bits
        $set->unset(5);
        $this->assertTrue($set->isEmpty());
    }

    /**
     * Test mutating intersect operation returns left operand
     *
     */
    public function testMutatingIntersect(): void {
        $alpha1 = new BitSet(256);
        $alpha2 = new BitSet(256);
        $empty = $alpha1->intersect($alpha2, true);
        $this->assertTrue($empty->isEmpty());
        $this->assertSame($alpha1, $empty);
    }

    /**
     * Test non-mutating complement operation
     *
     */
    public function testComplement(): void {
        $alpha = new BitSet(256);
        $complement = $alpha->complement();
        for ($i = 0; $i < 256; $i++) {
            $this->assertTrue($complement->test($i));
        }
        $this->assertTrue($alpha->isEmpty());
        $this->assertFalse($complement->isEmpty());
    }

    /**
     * Test mutating complement operation
     *
     */
    public function testMutatingComplement(): void {
        $alpha = new BitSet(256);
        $complement = $alpha->complement(true);
        for ($i = 0; $i < 256; $i++) {
            $this->assertTrue($complement->test($i));
        }
        $this->assertFalse($alpha->isEmpty());
        $this->assertFalse($complement->isEmpty());
        $this->assertSame($alpha, $complement);
    }
}
