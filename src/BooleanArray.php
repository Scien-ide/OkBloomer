<?php

namespace OkBloomer;

use InvalidArgumentException;
use ArrayAccess;
use Countable;

use function chr;
use function ord;
use function ceil;
use function str_repeat;

/**
 * Boolean Array
 *
 * A fixed array data structure that efficiently stores boolean values.
 *
 * @internal
 *
 * @category    Data Structures
 * @package     Scienide/OkBloomer
 * @author      Andrew DalPino
 *
 * @implements ArrayAccess<int, bool>
 */
class BooleanArray implements ArrayAccess, Countable
{
    /**
     * The number of bits in one byte.
     *
     * @var int
     */
    protected const ONE_BYTE = 8;

    /**
     * A byte array storing the bits of a bitmap.
     *
     * @var string
     */
    protected string $bitmap;

    /**
     * The number of elements in the array.
     *
     * @var int
     */
    protected int $size;

    /**
     * @param int $size
     * @throws \InvalidArgumentException
     */
    public function __construct(int $size)
    {
        if ($size < 0) {
            throw new InvalidArgumentException('size must be'
                . " greater than 0, $size given.");
        }

        $numBytes = (int) ceil($size / self::ONE_BYTE);

        $this->size = $size;
        $this->bitmap = str_repeat(chr(0), $numBytes);
    }

    /**
     * @param int $offset
     * @param bool $value
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value) : void
    {
        if (!$this->offsetExists($offset)) {
            throw new InvalidArgumentException("Element at offset $offset not found.");
        }

        $byteOffset = (int) ($offset / self::ONE_BYTE);

        $byte = ord($this->bitmap[$byteOffset]);

        $position = 2 ** ($offset % self::ONE_BYTE);

        if ($value) {
            $byte |= $position;
        } else {
            $byte &= 0xFF ^ $position;
        }

        $this->bitmap[$byteOffset] = chr($byte);
    }

    /**
     * Does a given row exist in the dataset.
     *
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        if ($offset >= 0 or $offset < $this->size) {
            return true;
        }

        return false;
    }

    /**
     * Return a row from the dataset at the given offset.
     *
     * @param int $offset
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function offsetGet($offset) : bool
    {
        if (!$this->offsetExists($offset)) {
            throw new InvalidArgumentException("Element at offset $offset not found.");
        }

        $byteOffset = (int) ($offset / self::ONE_BYTE);

        $byte = ord($this->bitmap[$byteOffset]);

        $position = 2 ** ($offset % self::ONE_BYTE);

        $bit = $position & $byte;

        return (bool) $bit;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset) : void
    {
        $this->offsetSet($offset, false);
    }

    /**
     * The number of elements that are stored in the array.
     *
     * @return int
     */
    public function count() : int
    {
        return $this->size;
    }
}
