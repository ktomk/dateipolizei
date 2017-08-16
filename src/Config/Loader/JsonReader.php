<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 11.08.17 18:16
 */

namespace Ktomk\DateiPolizei\Config\Loader;

use Ktomk\DateiPolizei\Config\Memory;

class JsonReader extends Memory
{
    const COMMENT = "_comment";

    /**
     * @var string
     */
    private $path;

    /**
     * @var ArrayUtil
     */
    private $util;

    public static function create(string $path): JsonReader
    {
        return new self($path);
    }

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->util = new ArrayUtil();
    }

    public function read(Memory $config): void
    {
        $result = $this->acquireJson();
        $config->array = $result;
    }

    public function merge(Memory $config, string $name): void
    {
        $result = $this->acquireJson();
        $array = &$config->array[$name];
        if (!isset($array)) {
            $array = [];
        }
        $array = $this->util->mergeRecursive($result, $array, true);
    }

    /**
     * Get file contents
     *
     * @return string
     */
    private function acquireBuffer(): string
    {
        $result = @file_get_contents($this->path);
        if (false === $result) {
            throw new \RuntimeException(
                sprintf("Failed to read file: '%s'", $this->path)
            );
        }

        return $result;
    }

    /**
     * @return array
     */
    private function acquireJson(): array
    {
        $result = json_decode($this->acquireBuffer(), true);
        if (!is_array($result)) {
            throw new \UnexpectedValueException(
                sprintf("Failed to parse JSON from file: '%s'", $this->path)
            );
        }

        $stripped = $this->util->stripKeyRecursive($result, self::COMMENT);

        return $stripped;
    }

    public function toArray(): array
    {
        return $this->acquireJson();
    }
}
