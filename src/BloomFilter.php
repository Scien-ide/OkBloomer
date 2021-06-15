<?php

namespace OkBloomer;

use InvalidArgumentException;

use function count;
use function crc32;
use function round;
use function max;
use function log;
use function exp;
use function end;

/**
 * Bloom Filter
 *
 * A probabilistic data structure that estimates the prior occurrence of a given token.
 *
 * References:
 * [1] P. S. Almeida et al. (2007). Scalable Bloom Filters.
 *
 * @category    Data Structures
 * @package     Scienide/OkBloomer
 * @author      Andrew DalPino
 */
class BloomFilter
{
    /**
     * The maximum 32 bit integer.
     *
     * @var int
     */
    protected const MAX_32_BIT_INTEGER = 2147483647;

    /**
     * The maximum false positive rate to maintain.
     *
     * @var float
     */
    protected $maxFalsePositiveRate;

    /**
     * The number of hash functions used in the filter.
     *
     * @var int
     */
    protected int $numHashes;

    /**
     * The size of each layer of the filter in bits.
     *
     * @var int
     */
    protected $layerSize;

    /**
     * The size of each slice of each layer in bits.
     *
     * @var int
     */
    protected $sliceSize;

    /**
     * The layers of the filter.
     *
     * @var list<\OkBloomer\BooleanArray>
     */
    protected array $layers;

    /**
     * The number of elements in the Bloom filter.
     *
     * @var int
     */
    protected int $n = 0;

    /**
     * @param float $maxFalsePositiveRate
     * @param int $numHashes
     * @param int $layerSize
     * @throws \InvalidArgumentException
     */
    public function __construct(float $maxFalsePositiveRate, ?int $numHashes, int $layerSize)
    {
        if ($maxFalsePositiveRate < 0.0 or $maxFalsePositiveRate > 1.0) {
            throw new InvalidArgumentException('Max false positive rate'
                . "  must be between 0 and 1, $maxFalsePositiveRate given.");
        }

        if (isset($numHashes) and $numHashes < 1) {
            throw new InvalidArgumentException('Number of hashes'
                . " must be greater than 1, $numHashes given.");
        }

        if ($numHashes === null) {
            $numHashes = max(1, (int) log(1.0 / $maxFalsePositiveRate, 2));
        }

        if ($layerSize > self::MAX_32_BIT_INTEGER) {
            throw new InvalidArgumentException('Layer size must be'
                . ' less than ' . self::MAX_32_BIT_INTEGER
                . ", $layerSize given.");
        }

        if ($layerSize < $numHashes) {
            throw new InvalidArgumentException('Layer size must be'
                . " greater than $numHashes, $layerSize given.");
        }

        $sliceSize = (int) round($layerSize / $numHashes);

        $this->maxFalsePositiveRate = $maxFalsePositiveRate;
        $this->numHashes = $numHashes;
        $this->layerSize = $layerSize;
        $this->sliceSize = $sliceSize;
        $this->layers = [new BooleanArray($layerSize)];
    }

    /**
     * Return the maximum false positive rate of the filter.
     *
     * @return float
     */
    public function maxFalsePositiveRate() : float
    {
        return $this->maxFalsePositiveRate;
    }

    /**
     * Return the number of hash functions used in the filter.
     *
     * @return int
     */
    public function numHashes() : int
    {
        return $this->numHashes;
    }

    /**
     * Return the size of each bitmap.
     *
     * @return int
     */
    public function layerSize() : int
    {
        return $this->layerSize;
    }

    /**
     * Return the number of layers in the filter.
     *
     * @return int
     */
    public function numLayers() : int
    {
        return count($this->layers);
    }

    /**
     * Return the size of the Bloom filter in bits.
     *
     * @return int
     */
    public function size() : int
    {
        return $this->numLayers() * $this->layerSize;
    }

    /**
     * Return the proportion of bits that are unset.
     *
     * @return float
     */
    public function capacity() : float
    {
        return 1.0 - ($this->n / $this->size());
    }

    /**
     * Return the number of bits that are set in the filter.
     *
     * @return int
     */
    public function n() : int
    {
        return $this->n;
    }

    /**
     * Return the probability of a recording a false positive.
     *
     * @return float
     */
    public function falsePositiveRate() : float
    {
        return (1.0 - exp(-(($this->numHashes * $this->n) / $this->size()))) ** $this->numHashes;
    }

    /**
     * Does a token exist in the filter?
     *
     * @param string $token
     * @return bool
     */
    public function exists(string $token) : bool
    {
        $hashes = $this->hashes($token);

        foreach ($this->layers as $layer) {
            foreach ($hashes as $hash) {
                if (!$layer[$hash]) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Does a token exist in the filter? If so, return true or insert and return false.
     *
     * @param string $token
     * @return bool
     */
    public function existsOrInsert(string $token) : bool
    {
        $hashes = $this->hashes($token);

        $q = count($this->layers) - 1;

        for ($i = 0; $i < $q; ++$i) {
            $layer = $this->layers[$i];

            foreach ($hashes as $hash) {
                if (!$layer[$hash]) {
                    continue 2;
                }
            }

            return true;
        }

        /** @var \OkBloomer\BooleanArray $layer */
        $layer = end($this->layers);

        $exists = true;

        foreach ($hashes as $hash) {
            if (!$layer[$hash]) {
                $layer[$hash] = true;

                ++$this->n;

                $exists = false;
            }
        }

        if (!$exists) {
            if ($this->falsePositiveRate() > $this->maxFalsePositiveRate) {
                $this->layers[] = new BooleanArray($this->layerSize);
            }
        }

        return $exists;
    }

    /**
     * Return an array of hashes from a given string.
     *
     * @param string $token
     * @return list<int>
     */
    protected function hashes(string $token) : array
    {
        $hashes = [];

        for ($i = 1; $i <= $this->numHashes; ++$i) {
            $hash = crc32("{$i}{$token}");

            $hash %= $this->sliceSize;
            $hash *= $i;

            $hashes[] = (int) $hash;
        }

        return $hashes;
    }
}
