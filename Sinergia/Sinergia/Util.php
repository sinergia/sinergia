<?php

namespace Sinergia\Sinergia;

use ReflectionMethod,
    ReflectionFunction,
    ReflectionFunctionAbstract,
    Closure;

class Util
{
    /**
     * Reflects anything that is callable
     * 'function_name'
     * 'Static::method'
     * array($object, 'method')
     * array('Class', 'method')
     * $Invokeable
     * function() {} closure
     * @param $callable
     * @return ReflectionFunction|ReflectionMethod
     */
    public static function callableToReflection($callable)
    {
        if ( is_string($callable) ) {
            if ( function_exists($callable) ) {
                return new ReflectionFunction($callable);
            }
            list($class, $method) = explode("::", $callable);

            return new ReflectionMethod($class, $method);
        }

        if ( is_array($callable) ) {
            list($class, $method) = $callable;

            return new ReflectionMethod($class, $method);
        }

        if ( is_object($callable) ) {
            if ($callable instanceof Closure) {
                return new ReflectionFunction($callable);
            }

            return new ReflectionMethod($callable, '__invoke');
        }
    }

    /**
     * Returns the function parameters as a dictionary with name => default value
     * @param  ReflectionFunctionAbstract $r
     * @return array
     */
    public static function getParameters(ReflectionFunctionAbstract $r)
    {
        $parameters = array();
        foreach ($r->getParameters() as $p) {
            $parameters[$p->getName()] = $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null;
        }

        return $parameters;
    }

    /**
     * Default fatal error
     * @var \Closure $fatal_error_handler
     */
    public static $fatal_error_handler = array(__CLASS__, 'fatal_error_handler');

    /**
     * @see https://github.com/freddiefrantzen/e2ex
     * Permite capturar erro fatal, ÚNICA MANEIRA!
     * Não consegue capturar erros de parse
     * Faz o mesmo que ob_start, mas permite exibir apenas o erro fatal e logar caso seja necessário.
     */
    public static function ob_start($fatal_error_handler = null)
    {
        $fatal_error_handler = $fatal_error_handler ?: static::$fatal_error_handler;
        ob_start( function($output) use ($fatal_error_handler) {
            // se o erro for fatal, não adianta fazer nada, debug_backtrace ou exception,
            // só resta logar e retornar a string contendo o erro fatal.
            $error = error_get_last();

            return @$error['type'] == E_ERROR
                ? call_user_func($fatal_error_handler, $error)
                : $output; // se não houve erro, retorna normalmente
        });
    }

    /**
     * Handler padrão para tratamento de erro fatal,
     * @param  array  $error array retornado por error_get_last()
     * @return string para ser exibida na tela.
     * @TODO implementar LOG
     * @TODO capture code around
     */
    public static function fatal_error_handler($error)
    {
        @header("Content-Type: text/plain");
        extract($error);

        return "\nErro Fatal:\n$message\n$file:$line\n";
    }
}
