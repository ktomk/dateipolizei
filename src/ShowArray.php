<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 25.07.17 21:43
 */

namespace Ktomk\DateiPolizei;


use ArrayAccess;

final class ShowArray implements ArrayAccess
{
    /**
     * @var array|bool[]
     */
    private $defines = [];

    /**
     * default values, @see reset
     *
     * @var array|bool[]
     */
    private $defaults = null;

    /**
     * offset cache
     *
     * @var array|string[]
     */
    private $offsets = [];

    public static function create(iterable $defines)
    {
        $arr = new self();

        foreach ($defines as $key => $value) {
            $arr->define($key, $value);
        }
        $arr->freezeDefaults();

        return $arr;
    }

    private function __construct()
    {
    }

    /**
     * @see reset
     */
    private function freezeDefaults()
    {
        $this->defaults = $this->defines;
    }

    /**
     * Reset array to default values
     *
     * @see freezeDefaults
     * @see setAll
     */
    public function reset(): ShowArray
    {
        $this->defines = $this->defaults;

        return $this;
    }

    /**
     * Are all of the chars set?
     *
     * @param string $chars
     * @return bool
     */
    public function areThese(string $chars): bool
    {
        $bool = true;

        foreach (str_split($chars) as $char) {
            if (!strlen($char)) {
                $bool = false;
                continue;
            }
            $bool = ($bool and $this->offsetGet($char));
        }

        return $bool;
    }

    /**
     * Is any of the chars set?
     *
     * @param string $chars
     * @return bool
     */
    public function isAny(string $chars): bool
    {
        foreach (str_split($chars) as $char) {
            if (!strlen($char)) {
                return false;
            }
            if ($this->offsetGet($char)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set all values
     *
     * Note: @see reset to reset to default values
     *
     * @param bool $value
     * @return $this
     */
    public function setAll(bool $value): ShowArray
    {
        foreach (array_keys($this->defines) as $key) {
            $this->defines[$key] = $value;
        }
        return $this;
    }

    /**
     * @param string $chars
     * @param bool $value
     * @return $this
     */
    public function setChars(string $chars, bool $value = true) {
        foreach (str_split($chars) as $char) {
            strlen($char) && $this->offsetSet($char, $value);
        }
        return $this;
    }

    private function define(string $name, ?bool $default = null)
    {
        $this->defines[$name] = $default ?? null;
    }

    /* @see \ArrayAccess implementation
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (isset($this->offsets[$offset])) {
            return true;
        }

        $length = strlen($offset);
        foreach (array_keys($this->defines) as $key) {
            if ($length > strlen($key)) {
                continue;
            }
            if (substr($key, 0, $length) === $offset) {
                $this->offsets[$offset] = $key;
                return true;
            }
        }

        return false;
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetGet($offset): bool
    {
        if (!$this->offsetExists($offset)) {
            throw new \BadMethodCallException(sprintf("Undefined offset '%s'", $offset));
        }
        return $this->defines[$this->offsets[$offset]];
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     * @throw \BadMethodCallException if offset can not be resolved to a show option
     */
    public function offsetSet($offset, $value): void
    {
        if (!$this->offsetExists($offset)) {
            throw new \BadMethodCallException(sprintf("Undefined offset '%s'", $offset));
        }

        $this->defines[$this->offsets[$offset]] = (bool) $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     * @throws \BadMethodCallException always as no offset can be unset
     */
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(
            sprintf("Can never unset an offset., not even '%s'", $offset)
        );
    }
}
