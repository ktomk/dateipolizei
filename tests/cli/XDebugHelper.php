<?php declare(strict_types=1);

/*
 * dateipolizei
 */

namespace Ktomk\DateiPolizei\CliTest;

/**
 * XDebugHelper
 *
 * Helps to gather PHP CLI options to pass Xdebug runtime configuration to
 * a PHP CLI sub-process so that debugging w/ Xdebug is transparent.
 * W/o passing the arguments, no debugging is available within the sub-process.
 *
 * Used in testing so that if something unexpected occurs, it is possible to
 * make use of the Xdebug step-debugger
 *
 * @see PhpRunner
 *
 * TODO(tk): Support code coverage with Phpunit, e.g. xdebug.coverage_enable
 */
class XDebugHelper
{
    /**
     * PHP extension name of the Xdebug extension
     */
    const EXT_NAME = 'xdebug';

    static $instance;

    public static function args(): string
    {
        self::$instance || self::$instance = new self();

        return self::$instance->getArgs();
    }

    public function isLoaded()
    {
        return extension_loaded(self::EXT_NAME);
    }

    private $args;

    /**
     * Get all interesting xdebug arguments for remote debugging in form
     * of a string that can be used for commandline arguments with the
     * PHP cli binary
     *
     * Returns an empty string if xdebug is not loaded
     *
     * @return string
     */
    public function getArgs(): string
    {
        return $this->args
            ?? $this->args = $this->getArgsImplementation();

    }

    private function getArgsImplementation(): string
    {
        if (false === $this->isLoaded()) {
            return "";
        }

        // -dzend_extension=xdebug.so
        $params = (array)$this->getExtension();

        // -dxdebug.remote_enable=1 -dxdebug.remote_mode=req
        // -dxdebug.remote_port=9000 -dxdebug.remote_host=127.0.0.1

        $settings = [
            'xdebug.remote_enable',
            'xdebug.remote_mode',
            'xdebug.remote_port',
            'xdebug.remote_host',
        ];

        $ini = ini_get_all(self::EXT_NAME, false);

        foreach ($settings as $name) {
            $params[] = sprintf('-d%s=%s', $name, $ini[$name]);
        }

        return implode(' ', $params);
    }

    /**
     * get extension parameter (if needed)
     */
    public function getExtension(): ?string
    {
        $search = $this->searchIni();


        if (count($search) === 0) {
            # there is nothing what could be done
            return null;
        }

        $potential = [];
        foreach ($search as [$enabled, $extension]) {
            if ($enabled) {
                # enabled by default, so nothing to care about
                return null;
            }
            $potential[$extension] = true;
        }

        # more than one potential is a sign for issues, unhandled

        $extension = array_keys($potential)[0];

        return sprintf('-dzend_extension=%s', escapeshellarg($extension));
    }

    /**
     * Pathnames of all ini files loaded in the current PHP process
     *
     * @return array|string[]
     */
    public function getIniPathnames(): array
    {
        return array_merge(
            [php_ini_loaded_file()],
            explode(",\n", rtrim(php_ini_scanned_files(), "\n"))
        );
    }

    /**
     * Search all php.ini files for the xdebug extension
     *
     * @return array [$enabled, $extension, $ini]
     */
    public function searchIni(): array
    {
        $iniFiles = $this->getIniPathnames();

        // TODO(tk): _ts to cover thread safe setups
        $pattern = sprintf(
            '~^\\s*([;#]?)\\s*zend_extension\\s*=\\s*(.*%s.*)\s*$~i',
            preg_quote(self::EXT_NAME, "~")
        );

        $extensionDir = ini_get('extension_dir');

        $foundInIni = [];

        foreach ($iniFiles as $ini) {
            if (!is_readable($ini)) {
                continue;
            }

            $lines = preg_grep($pattern, file($ini, FILE_IGNORE_NEW_LINES));
            if (!$lines) {
                continue;
            }

            foreach ($lines as $line) {
                preg_match($pattern, $line, $matches);
                list(, $comment, $extension) = $matches;

                # trim and unquote
                $extension = trim($extension);
                if ('"' === $extension[0] && '"' === substr($extension, -1)) {
                    $extension = substr($extension, 1, -1);
                }

                $enabled = !strlen($comment);
                // TODO(tk): Windows support for absolute pathnames with drive (c:\...)
                $path = ($extension[0] === '/')
                    ? $extension
                    : ($extensionDir . '/' . $extension);

                $exists = file_exists($path);
                if (false === $exists) {
                    # wrong configuration can be ignored
                    continue;
                }

                $foundInIni[] = [
                    $enabled,
                    $extension,
                    $ini,
                ];
            }
        }

        return $foundInIni;
    }
}
