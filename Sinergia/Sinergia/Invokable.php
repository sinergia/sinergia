<?php

namespace Sinergia\Sinergia;

use ReflectionMethod,
    ReflectionFunction,
    ReflectionFunctionAbstract,
    Closure;

/**
 * Wrap something callable into an Invokable
 * Class Invoker
 * @package Sinergia
 */
class Invokable
{
    /**
     * @var ReflectionFunction|ReflectionMethod
     */
    protected $reflection;
    protected $callable;
    protected $params;

    /**
     * 'function_name'
     * 'Static::method'
     * array($object, 'method')
     * array('Class', 'method')
     * $Invokable
     * function() {} closure
     *
     * @param $callable
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
    }

    public function getParams()
    {
        return $this->params ?:
               $this->params = static::getReflectionParams($this->getReflection());
    }

    public function getReflection()
    {
        return $this->reflection ?:
               $this->reflection = static::callableToReflection($this->callable);
    }

    public function __invoke($args = array())
    {
        $args = array_merge($this->getParams(), $args);

        return is_callable($this->callable)
               ? call_user_func_array($this->callable, $args)
               : null;
    }

    /**
     * Reflects anything that is callable
     * 'function_name'
     * 'Static::method'
     * array($object, 'method')
     * array('Class', 'method')
     * $Invokable
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
    public static function getReflectionParams(ReflectionFunctionAbstract $r)
    {
        $parameters = array();
        foreach ($r->getParameters() as $p) {
            $parameters[$p->getName()] = $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null;
        }

        return $parameters;
    }


}
