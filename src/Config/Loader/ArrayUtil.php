<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 11.08.17 20:23
 */

namespace Ktomk\DateiPolizei\Config\Loader;

/**
 * Class ArrayUtil
 *
 * Set / Merge / Append config array structures, incl. recursion
 */
class ArrayUtil
{

    /**
     * Strip key from array in recursive manner
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    public function stripKeyRecursive(array $array, string $key)
    {
        $return = [];
        foreach ($array as $k => $v) {
            if ($key === $k) {
                continue; // filter $key in question
            }

            if (is_array($v) && $v) {
                $v = $this->stripKeyRecursive($v, $key);
            }
            $return[$k] = $v;
        }

        return $return;
    }

    /**
     * Merge array into another one
     *
     * Maps are expected to be merged (only named items), if an existing
     * item is a string, then it will be extended into an array containing
     * values. If the merge into is a map, overwriting will occur only if
     * the $force parameter is set, otherwise an exception is thrown.
     *
     * If a map is merged into an existing map and the same keys exist, the
     * resolution is delegated to the recursive call preserving the $force
     * parameter.
     *
     * @param array $subject
     * @param array $into
     * @param bool $force [optional] force overwriting with non-merge-able types
     * @return array
     * @throws \Exception
     */
    public function mergeRecursive(array $subject, array $into, bool $force = false)
    {
        $merger = new ArrayMerger();
        return $merger->merge($subject, $into, $force);
    }
}
