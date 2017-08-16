<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 13.08.17 17:30
 */

namespace Ktomk\DateiPolizei\Config\Loader;

/**
 * Class ArrayMerger
 *
 * Merge two arrays with each other
 */
class ArrayMerger
{
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
    public function merge(array $subject, array $into, bool $force = false)
    {
        if ($this->isList($into)) {
            if (!$this->isList($subject)) {
                if (!($force || 0 === count($into))) {
                    throw new \UnexpectedValueException(
                        'Data loss: Can not overwrite list with non-list unless $force'
                    );
                }

                return $subject; # force overwrite list with map
            }

            return $this->mergeList($subject, $into);
        }

        if ($this->isList($subject)) {
            if (!$force) {
                throw new \UnexpectedValueException(
                    'Data loss: Can not overwrite list unless $force'
                );
            }

            return $subject; # force overwrite map with list
        }

        return $this->mergeMap($subject, $into, $force);
    }

    /**
     * @param array $subject
     * @param $into
     * @param bool $force
     * @return array
     */
    private function mergeMap(array $subject, array $into, bool $force): array
    {
        $keys = array_flip(array_keys($into));
        foreach ($subject as $k => $v) {

            // FIXME(tk): create subroutine to merge a node into a node

            // add as new entry if non yet exists
            if (!isset($keys[$k])) {
                $into[$k] = $v;
                continue;
            }

            if (!is_array($v) || !is_array($into[$k])) {
                $into[$k] = $this->mergeNonRecursive($v, $into[$k], $force);
                continue;
            }

            if ($this->isMap($v) && $this->isMap($into[$k])) {
                $into[$k] = $this->merge($v, $into[$k]);
                continue;
            }

            if ($this->isList($v) && $this->isList($into[$k])) {
                $into[$k] = $this->mergeList($v, $into[$k]);
                continue;
            }

            if (!$force) {
                throw new \UnexpectedValueException(
                    'Data loss: Can not overwrite list/map unless $force'
                );
            }

            $into[$k] = $v;
        }

        return $into;
    }

    public function mergeNonRecursive($subject, $into, bool $force)
    {

        // merge new value into existing entry
        if (!is_array($subject)) {
            if (!is_array($into)) {
                // TODO(tk): a flag might be useful to hint wanted behavior, overwriting might be wanted
                return $into === $subject ? $subject : [$into, $subject]; # turn into list if different values
            }
            if ($this->isList($into)) {
                return $this->mergeList((array)$subject, $into);
            }
            if (!$force) {
                throw new  \UnexpectedValueException(
                    'Data loss: Can not overwrite map with value unless $force'
                );
            }
            return $subject;
        }

        // merge list / map into existing non-list entry
        if (!is_array($into)) {
            if ($this->isList($subject)) {
                // TODO(tk): a flag might be useful to hint wanted behavior, overwriting might be wanted
                return $this->mergeList($subject, (array)$into);
            }
            if (!$force) {
                throw new \UnexpectedValueException(
                    'Data loss: Can not overwrite value with map unless $force'
                );
            }
            return $subject;
        }

        throw new \BadMethodCallException('merge is not non-recursive');
    }

    /**
     * Merge two lists
     *
     * @param array $subject
     * @param array $list
     * @return array
     */
    private function mergeList(array $subject, array $list): array
    {
        $array = array_merge($list, $subject);
        $array = array_unique($array);
        $array = array_values($array);

        return $array;
    }

    public function hasChildren($node): bool
    {
        if (!is_array($node)) {
            return false;
        }
        if ($this->isList($node)) {
            return false;
        }
        return (bool)count($node);
    }

    /**
     * Whether an array is a list (otherwise it's a map)
     *
     * Note: If the array to check is empty, it could signal a map, too but
     *       the result of this method prefers it to be a list - @see isMap
     *
     * @param array $array
     * @return bool
     */
    public function isList(array $array): bool
    {
        $keys = array_keys($array);
        $filtered = array_filter($keys, 'is_int');
        return count($filtered) === count($keys);
    }

    /**
     * Whether an array is a map (otherwise it's a list)
     *
     * Note: If the array to check is empty, it could signal a list, too but
     *       the result of this method prefers it to be a map - @see isList
     *
     * @param array $array
     * @return bool
     */
    public function isMap(array $array): bool
    {
        return !$array ?: !$this->isList($array);
    }
}
