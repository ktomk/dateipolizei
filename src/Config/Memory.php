<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 11.08.17 17:59
 */

namespace Ktomk\DateiPolizei\Config;


/**
 * Class Memory
 *
 * Shallow class to extend from for config-family classes to access the
 * config in memory which is just an associative array
 */
class Memory
{
    /**
     * configuration store in memory
     *
     * @var array
     */
    protected $array = [];
}
