<?php

namespace OkBloomer\Benchmarks;

/**
 * @BeforeMethods({"setUp"})
 */
class HashFunctionsBench
{
    private const BASES = [
        'A', 'C', 'G', 'T',
    ];

    private const NUM_SEQUENCES = 10000;

    private const K = 25;

    /**
     * @var list<string>
     */
    protected $sequences;

    /**
     * Generate a k-mer of length k.
     *
     * @param int $k
     * @return string
     */
    private static function generateKmer(int $k) : string
    {
        $sequence = '';

        for ($i = 0; $i < $k; ++$i) {
            $sequence .= self::BASES[rand(0, 3)];
        }

        return $sequence;
    }

    public function setUp() : void
    {
        $sequences = [];

        for ($i = 0; $i < self::NUM_SEQUENCES; ++$i) {
            $sequences[] = self::generateKmer(self::K);
        }

        $this->sequences = $sequences;
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function adler32() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = hash('adler32', $sequence);
        }
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function crc32() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = crc32($sequence);
        }
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function crc32b() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = hash('crc32b', $sequence);
        }
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function crc32c() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = hash('crc32c', $sequence);
        }
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function fnv132() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = hash('fnv132', $sequence);
        }
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function fnv1a32() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = hash('fnv1a32', $sequence);
        }
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function fnv164() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = hash('fnv164', $sequence);
        }
    }

    /**
     * @Subject
     * @revs(50)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function fnv1a64() : void
    {
        foreach ($this->sequences as $sequence) {
            $digest = hash('fnv1a64', $sequence);
        }
    }
}
