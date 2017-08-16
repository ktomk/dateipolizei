<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 15.08.17 19:57
 */

namespace Ktomk\DateiPolizei;

use Ktomk\DateiPolizei\String\Matcher\PatternMatcher;

/**
 * Class DapoConfig
 *
 * Dateipolizei main config object. Provides higher level primitives
 * used within the application and commands.
 *
 * Collaborates with DapoArgs and via that with all commands.
 */
class DapoConfig
{
    /**
     * @var Config
     */
    private $config;

    public function __construct()
    {
        # load config
        $loader = new Config\Loader();
        $config = new Config();
        $loader->loadInto($config);
        $this->config = $config;
    }

    public function getIgnore(): PatternMatcher
    {
        $matcher = new PatternMatcher();
        $config = $this->config;
        $patterns = $config->access('sets', 'ignore');
        assert(
            null !== $patterns,
            'Assert that default config has a set name "ignore"'
        );
        $matcher->addPatterns($patterns);

        return $matcher;
    }
}
