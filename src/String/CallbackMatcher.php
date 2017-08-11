<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 14.08.17 22:51
 */

namespace Ktomk\DateiPolizei\String;


class CallbackMatcher implements Matcher
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function match(string $subject): bool
    {
        $return = call_user_func($this->callback, $subject);
        assert(
            is_bool($return),
            'Assert that callback for matcher returns bool, '
            . gettype($return) . ' '
            . var_export($return, true) . ' given'
        );
        return (bool)$return;
    }
}
