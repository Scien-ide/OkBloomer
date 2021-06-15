<?php

namespace OkBloomer\Tests;

use OkBloomer\BloomFilter;
use PHPUnit\Framework\TestCase;

/**
 * @group Base
 * @covers \OkBloomer\BloomFilter
 */
class BloomFilterTest extends TestCase
{
    /**
     * @var \OkBloomer\BloomFilter
     */
    protected $filter;

    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->filter = new BloomFilter(0.001, 3, 1024);
    }

    /**
     * @test
     */
    public function existsOrInsert() : void
    {
        $this->assertFalse($this->filter->existsOrInsert('foo'));

        $this->assertFalse($this->filter->existsOrInsert('bar'));

        $this->assertTrue($this->filter->exists('foo'));
    }
}
