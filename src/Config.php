<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 11.08.17 16:14
 */

namespace Ktomk\DateiPolizei;

/**
 * Class Config
 *
 * Dateipolizei config object
 */
final class Config extends Config\Memory
{
    public function toArray(): array
    {
        return $this->array;
    }

    /**
     * Access config element via keys, returns NULL if the element
     * is not set.
     *
     * @param array ...$keys
     *
     * @return mixed
     */
    public function access(...$keys)
    {
        $ref = $this->array;
        foreach ($keys as $key) {
            if (!isset($ref[$key])) {
                return null;
            }
            $ref = $ref[$key];
        }

        return $ref;
    }
}
