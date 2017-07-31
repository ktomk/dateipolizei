<?php

/*
 * dateipolizei
 */

namespace Ktomk\DateiPolizei\String;


interface Matcher
{
    /**
     * Match a string
     *
     * @param string $subject
     * @return bool true for a match, false otherwise
     */
    public function match(string $subject): bool;
}
