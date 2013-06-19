<?php

namespace Sinergia;

class Error
{
    protected static $stack = array();
    /**
     * utilizar restore_error_handler() para reverter
     */
    public static function toException()
    {
        set_error_handler(function($level, $message, $file, $line) {
            if (error_reporting() === 0) return false;
            if (error_reporting() & $level) {
                throw new \ErrorException(Error::str($level).": $message", $level, 0, $file, $line);
            }

            return false;
        });
    }

    public static function enableDefaultExceptionHandler()
    {
        set_exception_handler(function($e) {
            @header("Content-Type: text/plain");
            printf("\nDefault Exception Handler: %s\n%s\n", __CLASS__, $e);
        });
    }

    public static function enable($error)
    {
        static::push(error_reporting() | $error);
    }

    public static function push($error)
    {
        static::$stack[] = error_reporting();
        error_reporting($error);
    }

    public static function pop()
    {
        error_reporting(array_pop(static::$stack));
    }

    public static function disable($error)
    {
        static::push(error_reporting() & ~$error);
    }

    public static function str($error)
    {
        $errors = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            E_ALL => 'E_ALL'
        );

        return $errors[$error];
    }
}
