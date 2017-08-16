<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 16.08.17 09:00
 */

namespace Ktomk\DateiPolizei\Config;

class TestConfigHelper extends Memory
{
    public static function setArray(Memory $component, array $array)
    {
        $component->array = $array;
    }
}
