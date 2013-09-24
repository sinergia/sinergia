<?php

namespace Sinergia\Sinergia;

use DomainException;

class RouteMatcher
{
    public static function findMatch($routes, $path)
    {
        foreach ($routes as $route => $handler) {
            if ( ! is_null($parameters = static::match($route, $path)) ) {
                return array($handler, $parameters);
            }
        }

        return null;
    }

    public static function match($pattern, $subject)
    {
        // if $pattern starts with /, it's a simple router expression
        if ($pattern[0] == '/') {
            $pattern = trim($pattern, '/');
            $pattern = static::regex($pattern); // convert it back to regular expression
        }

        $return = @preg_match("!^$pattern$!", $subject, $matches);

        if ($return === false) {
            throw new DomainException("bad regular expression '$pattern'");
        }

        if ($return == 0) {
            return null;
        }

        // remove numeric keys
        $parameters = array_diff_key($matches, range(0, count($matches)-1));

        return $parameters;
    }

    /**
     * Converts a easy route expression to regular expression used by the match method
     * @param  string $expression
     * @return string
     */
    public static function regex($expression)
    {
        $rules = array(
            '!\)!'      => ')?', // optional pattern
            '!\.!'      => '\.', // must came after any dot patterns above
            '!\*(\w+)!' => '(?<$1>.+?)', // star pattern
            '!\*!'      => '(?<slug>.+?)', // auto name slug for star pattern
            '!:(\w+)!'  => '(?<$1>[^/]+?)', // :id
            '!\{(\w+)\?\}!' => '?(?<$1>[^/]+?)?', // laravel optional {id?}
            '!\{(\w+)\}!' => '(?<$1>[^/]+?)', // laravel {id}
        );

        $pattern = preg_replace(array_keys($rules), $rules, $expression);

        return $pattern;
    }
}
