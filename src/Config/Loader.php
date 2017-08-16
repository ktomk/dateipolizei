<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 13.08.17 18:07
 */

namespace Ktomk\DateiPolizei\Config;

use Ktomk\DateiPolizei\Config;
use Ktomk\DateiPolizei\Config\Loader\{
    ArrayMerger, JsonReader
};

/**
 * Class Loader
 *
 * Default config loading
 */
class Loader extends Memory
{
    const BASE_DIR = __DIR__ . '/../..';

    public function loadInto(Memory $config)
    {
        $array = [];
        $merger = new ArrayMerger();

        $base = self::BASE_DIR;

        $array['file-types'] = JsonReader::create($base . '/data/file-types.json')->toArray();
        $array['sets'] = JsonReader::create($base . '/data/sets.json')->toArray();

        // TODO(tk): search config from working directory, inject it in ctor
        $localConfig = $base . '/dateipolizei.json';
        if (file_exists($localConfig) && is_readable($localConfig)) {
            $local = JsonReader::create($localConfig)->toArray();
            $array = $merger->merge($local, $array, false);
        }

        $config->array = $array;
    }

    public function createConfig(): Config
    {
        $config = new Config();
        $this->loadInto($config);

        return $config;
    }
}
